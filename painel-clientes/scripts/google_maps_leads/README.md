# Captação automática de leads — Google Places API

Este script busca restaurantes e comércios no Google Maps, filtra os que **não têm site**, e salva direto no painel como prospectos prontos para abordagem.

---

## Como funciona (o fluxo completo)

```
Script Python
    │
    ├── 1. Faz uma busca no Google Maps
    │       ex: "restaurante Pinheiros São Paulo"
    │
    ├── 2. Filtra os resultados
    │       ✗ tem site → ignora (já têm presença digital)
    │       ✗ rating < 4.0 → ignora (estabelecimento fraco)
    │       ✗ menos de 50 avaliações → ignora (pouco movimento)
    │       ✗ sem telefone → ignora (não tem como contatar)
    │       ✗ já existe no painel → ignora (duplicata)
    │
    └── 3. Salva os aprovados no banco
            → aparecem na Fila de Abordagem do painel
```

**Por que filtrar quem tem site?**
Porque o serviço que vendemos é criação de sites. Se o estabelecimento já tem, ele não é nosso cliente ideal.

**Por que exigir boas avaliações?**
Estabelecimento com muitas avaliações tem movimento, ou seja, tem dinheiro para investir. Cliente com 4,5⭐ e 300 avaliações é muito melhor do que um com 3,8⭐ e 20 avaliações.

---

## Antes de rodar pela primeira vez

### 1. Criar a API Key do Google

1. Acesse o [Google Cloud Console](https://console.cloud.google.com)
2. Crie um projeto novo (pode chamar de "Captação de Leads")
3. No menu lateral: **APIs e serviços → Biblioteca**
4. Busque por **Places API** e clique em **Ativar**
5. Vá em **APIs e serviços → Credenciais → Criar credencial → Chave de API**
6. Copie a chave gerada (começa com `AIza...`)

> **Sobre custos:** o Google dá $200 de crédito gratuito por mês. O script consome ~$15–20/mês, ou seja, está bem dentro do gratuito.

### 2. Instalar as dependências Python (só na primeira vez)

```powershell
cd painel-clientes\scripts\google_maps_leads
pip install -r requirements.txt
```

---

## Como rodar

O Docker do painel precisa estar rodando. Depois, no terminal:

```powershell
# 1. Defina a chave (só precisa fazer isso uma vez por sessão do terminal)
$env:GOOGLE_API_KEY = 'AIzaSua_chave_aqui'

# 2. Entre na pasta do script
cd painel-clientes\scripts\google_maps_leads

# 3. Rode
python scraper.py
```

O script vai printar cada lead encontrado em tempo real:

```
19:32:04  Buscando: restaurante Pinheiros São Paulo
19:32:06    20 resultados brutos
19:32:08  ✓  Restaurante Japonês Kioto  (4.8⭐  312 avaliações)
19:32:10  ✓  Pizzaria Artesanal Bella   (4.6⭐  189 avaliações)
...
19:45:22  ────────────────────────────────────────────────────────────
19:45:22  RESULTADO: 47 importado(s), 201 ignorado(s)
19:45:22    Com site:          120
19:45:22    Rating < 4.0:       38
19:45:22    Reviews < 50:       31
19:45:22    Sem telefone:        9
19:45:22    Duplicados:          3
```

Ao terminar, abra o painel em `localhost:8080/admin/prospects/queue` — os leads já aparecem na fila, ordenados do mais avaliado para o menos.

---

## Ajustar os filtros

No topo do `scraper.py`:

```python
MIN_RATING  = 4.0   # avaliação mínima (escala de 0 a 5)
MIN_REVIEWS = 50    # mínimo de avaliações no Google
```

---

## Adicionar novos bairros ou categorias

Cada linha da lista `SEARCHES` é uma busca independente no Google Maps:

```python
SEARCHES = [
    'restaurante Pinheiros São Paulo',
    'hamburgueria Vila Madalena São Paulo',
    'pizzaria artesanal Itaim Bibi São Paulo',
    # adicione quantas quiser...
]
```

Dica: seja específico na categoria. "restaurante" traz de tudo; "hamburgueria artesanal" ou "bistrô" traz um público mais segmentado.

---

## Agendar para rodar sozinho (opcional)

Se quiser que o script rode automaticamente toda semana sem precisar abrir o terminal:

**Windows — Agendador de Tarefas:**
1. Abra o **Agendador de Tarefas** (pesquise no menu iniciar)
2. Crie uma nova tarefa básica
3. Gatilho: semanal, toda segunda-feira às 07:00
4. Ação: `python C:\caminho\completo\para\scraper.py`
5. Nas propriedades, adicione a variável de ambiente `GOOGLE_API_KEY` com sua chave

---

## Perguntas frequentes

**O script apaga leads existentes?**
Não. Ele só adiciona. Leads já cadastrados (mesmo telefone ou mesmo nome) são ignorados automaticamente.

**Posso rodar mais de uma vez?**
Sim. As duplicatas são checadas antes de inserir.

**Por que alguns leads aparecem sem telefone no painel?**
Muitos estabelecimentos não cadastram o telefone no Google Maps. O script já filtra esses, mas se você encontrar um manualmente pode adicionar na ficha do prospecto.

**O que fazer com os leads depois de importar?**
Abra a Fila de Abordagem em `/admin/prospects/queue` e comece a contatar pelo WhatsApp. O painel já gera a mensagem personalizada para cada lead.
