# devspace

Monorepo da **devspace** — reúne o sistema interno da agência (painel de clientes + site institucional) e os projetos de clientes em desenvolvimento/manutenção.

Cada projeto roda isoladamente, com seu próprio `docker-compose.yml`, e possui documentação própria mais detalhada (ver links abaixo).

---

## Projetos

| Pasta | O que é | Stack | Documentação |
|---|---|---|---|
| [`painel-clientes/`](painel-clientes/) | Sistema interno da devspace: painel de gestão de projetos/tickets/contratos + site institucional da devspace | PHP 8.2 (CodeIgniter 4) + PostgreSQL + WordPress + MySQL + Nginx, tudo em Docker | [README](painel-clientes/README.md) · [CLAUDE.md](painel-clientes/CLAUDE.md) |
| [`vila-mentoria/`](vila-mentoria/) | Site WordPress do cliente **Vila Mentoria** (conteúdo editorial + podcast "Na Varanda") | WordPress 6.7 (tema filho `kava-child`) + MySQL, em Docker | — (ver seção abaixo) |

---

## painel-clientes

Monorepo interno com dois sistemas que se comunicam pela rede Docker:

- **Painel de Clientes** (CodeIgniter 4 + PostgreSQL) — gestão de projetos, tickets de suporte, financeiro, pipeline de vendas (prospectos), contratos com aceite digital e controle de acesso por papel (`admin`, `agent`, `client`).
- **Site devspace** (WordPress + tema `astra-child`) — site institucional com formulário de contato integrado ao painel, dark mode, SEO nativo (sem plugin) e banner de cookies (LGPD).

Detalhes completos de arquitetura, rotas, banco de dados e como subir o ambiente estão no [README do projeto](painel-clientes/README.md).

Inclui ainda um script auxiliar de captação de leads via Google Places API em [`scripts/google_maps_leads/`](painel-clientes/scripts/google_maps_leads/).

## vila-mentoria

Site WordPress para o cliente **Vila Mentoria**, com tema filho próprio (`kava-child`, sem Elementor) construído sobre o tema pai **Kava**.

- `front-page.php` — home reescrita do zero (hero, seção sobre, artigos em layout editorial, seção "Na Varanda" com episódios em destaque, contato, footer).
- `assets/css/main.css` — design system próprio (paleta verde escuro/dourado, tipografia Poppins/Montserrat).
- Fontes self-hosted (`assets/fonts/`) para eliminar dependência do Google Fonts.
- Ajustes de performance aplicados: WP Cron desativado (`DISABLE_WP_CRON`), OPcache habilitado via `docker-config/php-performance.ini` — TTFB reduzido de ~26s para ~1.7–1.9s.

### Como rodar

```powershell
cd vila-mentoria
docker-compose up -d
```

| URL | O que é |
|---|---|
| `http://localhost:8090` | Site Vila Mentoria |

> O `wp-content` é montado como volume local; plugins e temas de terceiros não são versionados (ver `.gitignore`), apenas o tema filho `kava-child` e as customizações.

---

## Como rodar tudo

Cada projeto é independente e usa portas diferentes, então dá pra rodar os dois ao mesmo tempo:

```powershell
# Painel + site devspace
cd painel-clientes
docker-compose up -d --build

# Site Vila Mentoria
cd ../vila-mentoria
docker-compose up -d
```

Pré-requisitos: Docker Desktop + WSL2 (Windows).

---

## Status

Projeto retomado em 2026-08-26 após pausa desde a última atividade (22/06/2026, ajustes de dark mode e SVG inline no `painel-clientes`). Working tree limpa, branch `main` sincronizada com `origin/main`.
