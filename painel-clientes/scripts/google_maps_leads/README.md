# Script de Captação — Google Places API

Busca restaurantes e comércios no Google Maps sem site e insere direto na tabela `prospects` do painel.

---

## 1. Criar API Key do Google

1. Acesse [console.cloud.google.com](https://console.cloud.google.com)
2. Crie um projeto (ou use um existente)
3. Vá em **APIs e serviços → Biblioteca**
4. Ative **Places API**
5. Vá em **APIs e serviços → Credenciais → Criar credencial → Chave de API**
6. Copie a chave gerada

> O Google oferece **$200 de crédito gratuito por mês**.  
> Para ~500 leads/semana o custo estimado é ~$15–20/mês — dentro do crédito gratuito.

---

## 2. Configurar o script

Abra `scraper.py` e preencha no topo:

```python
GOOGLE_API_KEY = 'sua_chave_aqui'

DB_CONFIG = dict(
    host     = 'localhost',
    port     = 5433,           # porta externa do Docker PostgreSQL
    dbname   = 'painel_clientes',
    user     = 'painel_user',
    password = 'painel_pass',
)
```

Ajuste a lista `SEARCHES` com os bairros e categorias que quer prospectar.

---

## 3. Instalar dependências e rodar

```bash
# Instalar dependências (uma vez)
pip install -r requirements.txt

# Rodar o script (com Docker do painel rodando)
python scraper.py
```

Saída esperada:

```
10:32:01  Buscando: restaurante Pinheiros São Paulo
10:32:03  60 resultados brutos
10:32:04  ✓  Restaurante Japonês Kioto  (4.8⭐  312 avaliações)
10:32:04  ✓  Pizzaria Artesanal Bella   (4.6⭐  189 avaliações)
...
10:45:22  ────────────────────────────────────────────────────────────
10:45:22  RESULTADO: 47 importado(s), 201 ignorado(s)
10:45:22    Com site:          120
10:45:22    Rating < 4.0:       38
10:45:22    Reviews < 50:       31
10:45:22    Sem telefone:        9
10:45:22    Duplicados:          3
```

Os leads importados aparecem automaticamente na **Fila de Abordagem** do painel.

---

## 4. Agendar execução semanal (opcional)

**Windows — Agendador de Tarefas:**

```
Ação: python C:\caminho\para\scraper.py
Gatilho: semanal, toda segunda-feira às 07:00
```

**Linux/Mac — cron:**

```bash
# Toda segunda-feira às 7h
0 7 * * 1 cd /caminho/scripts/google_maps_leads && python scraper.py >> scraper.log 2>&1
```

---

## Ajustar filtros

No topo do `scraper.py`:

```python
MIN_RATING  = 4.0   # avaliação mínima (0–5)
MIN_REVIEWS = 50    # número mínimo de avaliações
```

## Adicionar novos bairros/categorias

```python
SEARCHES = [
    'restaurante Pinheiros São Paulo',
    'hamburgueria Vila Madalena São Paulo',
    # adicione novas linhas aqui...
]
```

Cada linha é uma busca independente no Google Maps.  
O script desconsidera duplicatas automaticamente (mesmo telefone ou mesmo nome).
