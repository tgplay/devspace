#!/usr/bin/env python3
"""
Captação automática de leads via Google Places API
Insere prospectos diretamente na tabela prospects do painel CI4

Pré-requisitos:
    pip install requests psycopg2-binary
"""

import os
import time
import logging
import requests
import psycopg2

# =============================================================================
# CONFIGURAÇÃO — preencha antes de rodar
# =============================================================================

# Windows PowerShell: $env:GOOGLE_API_KEY = 'sua_chave'
# Linux/Mac:          export GOOGLE_API_KEY='sua_chave'
GOOGLE_API_KEY = os.environ['GOOGLE_API_KEY']

DB_CONFIG = dict(
    host     = 'localhost',
    port     = 5433,          # porta externa do container PostgreSQL
    dbname   = 'painel_clientes',
    user     = 'painel_user',
    password = 'painel_pass',
)

MIN_RATING  = 4.0
MIN_REVIEWS = 50

# Bairros × categorias — adicione, remova ou altere conforme a prospecção
SEARCHES = [
    'restaurante Pinheiros São Paulo',
    'hamburgueria Pinheiros São Paulo',
    'pizzaria artesanal Pinheiros São Paulo',
    'café gourmet Pinheiros São Paulo',
    'padaria boutique Pinheiros São Paulo',
    'bistrô Pinheiros São Paulo',

    'restaurante Vila Madalena São Paulo',
    'hamburgueria Vila Madalena São Paulo',
    'café Vila Madalena São Paulo',

    'restaurante Itaim Bibi São Paulo',
    'bistrô Itaim Bibi São Paulo',
    'hamburgueria Itaim Bibi São Paulo',

    'restaurante Moema São Paulo',
    'padaria boutique Moema São Paulo',

    'restaurante Jardins São Paulo',
    'restaurante Vila Olímpia São Paulo',

    'buffet infantil Pirituba São Paulo',
    'buffet festa Vila Pirituba infantil São Paulo', 
]

# =============================================================================

PLACES_URL = 'https://maps.googleapis.com/maps/api/place'

logging.basicConfig(
    level   = logging.INFO,
    format  = '%(asctime)s  %(message)s',
    datefmt = '%H:%M:%S',
)
log = logging.getLogger(__name__)


# ── Google Places API ─────────────────────────────────────────────────────────

def text_search(query: str) -> list:
    """Busca no Google Maps com paginação automática (até 60 resultados)."""
    results = []
    params  = {'query': query, 'language': 'pt-BR', 'key': GOOGLE_API_KEY}
    url     = f'{PLACES_URL}/textsearch/json'

    while True:
        resp = requests.get(url, params=params, timeout=10).json()
        if resp.get('status') != 'OK' and not results:
            log.warning(f'  API status: {resp.get("status")} — {resp.get("error_message", "")}')
        results.extend(resp.get('results', []))

        token = resp.get('next_page_token')
        if not token:
            break

        time.sleep(2)  # Google exige ~2s antes de usar o next_page_token
        params = {'pagetoken': token, 'key': GOOGLE_API_KEY}

    return results


def place_details(place_id: str) -> dict:
    """Busca detalhes completos: telefone, website e URL do Maps."""
    resp = requests.get(
        f'{PLACES_URL}/details/json',
        params={
            'place_id': place_id,
            'fields'  : 'name,formatted_phone_number,website,url',
            'language': 'pt-BR',
            'key'     : GOOGLE_API_KEY,
        },
        timeout=10,
    ).json()
    return resp.get('result', {})


# ── PostgreSQL ────────────────────────────────────────────────────────────────

def already_exists(cur, name: str, phone: str) -> bool:
    """Evita duplicatas verificando telefone (exato) e nome (case-insensitive)."""
    if phone:
        cur.execute("SELECT 1 FROM prospects WHERE phone = %s LIMIT 1", (phone,))
        if cur.fetchone():
            return True
    cur.execute(
        "SELECT 1 FROM prospects WHERE LOWER(name) = LOWER(%s) LIMIT 1",
        (name,)
    )
    return bool(cur.fetchone())


def insert_prospect(cur, name, phone, rating, reviews, maps_url):
    cur.execute("""
        INSERT INTO prospects
            (name, phone, interest, source, status,
             rating, reviews_count, maps_url, created_at, updated_at)
        VALUES
            (%s, %s, 'site', 'google_maps', 'new',
             %s, %s, %s, NOW(), NOW())
    """, (name, phone, rating, reviews, maps_url))


# ── Main ──────────────────────────────────────────────────────────────────────

def run():
    conn = psycopg2.connect(**DB_CONFIG)
    cur  = conn.cursor()

    imported = 0
    skipped  = {
        'com_site'      : 0,
        'rating_baixo'  : 0,
        'poucos_reviews': 0,
        'sem_telefone'  : 0,
        'duplicado'     : 0,
    }

    seen_place_ids = set()  # evita buscar detalhes do mesmo lugar duas vezes

    for query in SEARCHES:
        log.info(f'Buscando: {query}')
        candidates = text_search(query)
        log.info(f'  {len(candidates)} resultados brutos')

        for place in candidates:
            place_id = place.get('place_id', '')
            if place_id in seen_place_ids:
                continue
            seen_place_ids.add(place_id)

            rating  = float(place.get('rating') or 0)
            reviews = int(place.get('user_ratings_total') or 0)

            if rating  < MIN_RATING:  skipped['rating_baixo']    += 1; continue
            if reviews < MIN_REVIEWS: skipped['poucos_reviews']   += 1; continue

            time.sleep(0.1)  # respeita rate limit da API
            details = place_details(place_id)

            if details.get('website'):
                skipped['com_site'] += 1
                continue

            phone = (details.get('formatted_phone_number') or '').strip()
            if not phone:
                skipped['sem_telefone'] += 1
                continue

            name     = details.get('name') or place.get('name', '')
            maps_url = details.get('url', '')

            if already_exists(cur, name, phone):
                skipped['duplicado'] += 1
                continue

            insert_prospect(cur, name, phone, rating, reviews, maps_url)
            conn.commit()
            imported += 1
            log.info(f'  ✓  {name}  ({rating}⭐  {reviews:,} avaliações)')

    cur.close()
    conn.close()

    total_skipped = sum(skipped.values())
    log.info('─' * 60)
    log.info(f'RESULTADO: {imported} importado(s), {total_skipped} ignorado(s)')
    log.info(f'  Com site:          {skipped["com_site"]}')
    log.info(f'  Rating < {MIN_RATING}:     {skipped["rating_baixo"]}')
    log.info(f'  Reviews < {MIN_REVIEWS}:    {skipped["poucos_reviews"]}')
    log.info(f'  Sem telefone:      {skipped["sem_telefone"]}')
    log.info(f'  Duplicados:        {skipped["duplicado"]}')


if __name__ == '__main__':
    run()
