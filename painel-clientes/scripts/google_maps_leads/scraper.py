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
    host     = os.environ.get('DB_HOST', 'localhost'),
    port     = int(os.environ.get('DB_PORT', '5433')),   # porta externa do container PostgreSQL
    dbname   = os.environ.get('DB_NAME', 'painel_clientes'),
    user     = os.environ.get('DB_USER', 'painel_user'),
    password = os.environ['DB_PASS'],                    # obrigatória: defina DB_PASS no ambiente
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

PLACES_URL = 'https://places.googleapis.com/v1/places:searchText'

# Termos do Google Maps Platform: só o place_id pode ser armazenado sem prazo.
# Nota, telefone e site são usados apenas para FILTRAR e descartados; o painel busca
# nome/telefone ao vivo (por place_id) quando o lead é exibido.
FIELD_MASK = ('places.id,places.rating,places.userRatingCount,'
              'places.websiteUri,places.nationalPhoneNumber,nextPageToken')

logging.basicConfig(
    level   = logging.INFO,
    format  = '%(asctime)s  %(message)s',
    datefmt = '%H:%M:%S',
)
log = logging.getLogger(__name__)


# ── Google Places API (New) ───────────────────────────────────────────────────

def text_search(query: str) -> list:
    """Busca no Google Maps com paginação (até 60 resultados)."""
    results = []
    body    = {'textQuery': query, 'languageCode': 'pt-BR', 'regionCode': 'BR', 'pageSize': 20}
    headers = {'X-Goog-Api-Key': GOOGLE_API_KEY, 'X-Goog-FieldMask': FIELD_MASK}

    for _ in range(3):
        resp = requests.post(PLACES_URL, json=body, headers=headers, timeout=15)
        data = resp.json()
        if resp.status_code >= 400:
            log.warning(f'  API {resp.status_code}: {data.get("error", {}).get("message", "")}')
            break
        results.extend(data.get('places', []))

        token = data.get('nextPageToken')
        if not token:
            break
        body['pageToken'] = token
        time.sleep(0.3)

    return results


# ── PostgreSQL ────────────────────────────────────────────────────────────────

def already_exists(cur, place_id: str) -> bool:
    cur.execute("SELECT 1 FROM prospects WHERE google_place_id = %s LIMIT 1", (place_id,))
    return bool(cur.fetchone())


def insert_prospect(cur, place_id: str):
    cur.execute("""
        INSERT INTO prospects
            (name, interest, source, status, google_place_id,
             contact_source, created_at, updated_at)
        VALUES
            (%s, 'site', 'google_maps', 'new', %s,
             'Google Maps (dados públicos do estabelecimento)', NOW(), NOW())
    """, ('Lead Google Maps …' + place_id[-6:], place_id))


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

    seen_place_ids = set()

    for query in SEARCHES:
        log.info(f'Buscando: {query}')
        candidates = text_search(query)
        log.info(f'  {len(candidates)} resultados brutos')

        for place in candidates:
            place_id = place.get('id', '')
            if not place_id or place_id in seen_place_ids:
                continue
            seen_place_ids.add(place_id)

            rating  = float(place.get('rating') or 0)
            reviews = int(place.get('userRatingCount') or 0)

            if rating  < MIN_RATING:  skipped['rating_baixo']  += 1; continue
            if reviews < MIN_REVIEWS: skipped['poucos_reviews'] += 1; continue
            if place.get('websiteUri'):
                skipped['com_site'] += 1
                continue
            if not (place.get('nationalPhoneNumber') or '').strip():
                skipped['sem_telefone'] += 1
                continue

            if already_exists(cur, place_id):
                skipped['duplicado'] += 1
                continue

            insert_prospect(cur, place_id)
            conn.commit()
            imported += 1
            log.info(f'  ✓  …{place_id[-6:]}  ({rating}⭐  {reviews:,} avaliações)')

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
