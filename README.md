# devspace

Monorepo da **devspace** — reúne o sistema interno da agência (painel de clientes + site institucional).

Cada projeto roda isoladamente, com seu próprio `docker-compose.yml`, e possui documentação própria mais detalhada (ver links abaixo).

---

## Projetos

| Pasta | O que é | Stack | Documentação |
|---|---|---|---|
| [`painel-clientes/`](painel-clientes/) | Sistema interno da devspace: painel de gestão de projetos/tickets/contratos + site institucional da devspace | PHP 8.2 (CodeIgniter 4) + PostgreSQL + WordPress + MySQL + Nginx, tudo em Docker | [README](painel-clientes/README.md) · [CLAUDE.md](painel-clientes/CLAUDE.md) |

---

## painel-clientes

Monorepo interno com dois sistemas que se comunicam pela rede Docker:

- **Painel de Clientes** (CodeIgniter 4 + PostgreSQL) — gestão de projetos, tickets de suporte, financeiro, pipeline de vendas (prospectos), contratos com aceite digital e controle de acesso por papel (`admin`, `agent`, `client`).
- **Site devspace** (WordPress + tema `astra-child`) — site institucional com formulário de contato integrado ao painel, dark mode, SEO nativo (sem plugin) e banner de cookies (LGPD).

Detalhes completos de arquitetura, rotas, banco de dados e como subir o ambiente estão no [README do projeto](painel-clientes/README.md).

Inclui ainda um script auxiliar de captação de leads via Google Places API em [`scripts/google_maps_leads/`](painel-clientes/scripts/google_maps_leads/).

---

## Como rodar

```powershell
# Painel + site devspace
cd painel-clientes
docker-compose up -d --build
```

Pré-requisitos: Docker Desktop + WSL2 (Windows).

---

## Status

Projeto retomado em 2026-08-26 após pausa desde a última atividade (22/06/2026, ajustes de dark mode e SVG inline no `painel-clientes`). Working tree limpa, branch `main` sincronizada com `origin/main`.
