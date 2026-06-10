# CLAUDE.md — painel-clientes

Contexto do projeto para sessões Claude Code.

---

## O que é este projeto

Monorepo com dois sistemas:

| Sistema | Tecnologia | URL local |
|---|---|---|
| Painel admin/cliente | CodeIgniter 4 + PostgreSQL | `localhost:8080` (admin) / `localhost:8081` (cliente) |
| Site institucional | WordPress + MySQL | `localhost:8082` |

Tudo orquestrado por `docker-compose.yml`. Os serviços se comunicam pela rede interna Docker (`nginx`, `php`, `db`, `wordpress`, `mysql`).

---

## Credenciais de desenvolvimento

**PostgreSQL**
- Host: `localhost:5433` (externo) / `db:5432` (interno Docker)
- Usuário: `painel_user` / Senha: `painel_pass` / DB: `painel_clientes`
- Admin CI4: `admin@painel.local` / `admin123`

**MySQL (WordPress)**
- Serviço Docker: `mysql`
- Credenciais em `docker-compose.yml` (env vars `MYSQL_*`)

---

## Stack

- **Backend**: PHP 8.2 + CodeIgniter 4.7
- **Banco painel**: PostgreSQL 15
- **CMS**: WordPress 6.5 + tema Astra Child (`astra-child/`)
- **Banco WP**: MySQL 8.0
- **Frontend painel**: Bootstrap 5.3 + Bootstrap Icons
- **Servidor**: Nginx 1.25 (reverse proxy para PHP-FPM)
- **Ambiente**: Docker + Docker Compose

---

## Estrutura CI4 (`painel/app/`)

```
Controllers/
  Auth.php                  # login, logout, register
  Api/Contact.php           # endpoint público /api/contact/ticket (do site WP)
  Admin/
    Dashboard.php
    Clients.php             # CRUD clientes + impersonation
    Projects.php            # CRUD projetos + tarefas
    Support.php             # chamados de suporte
    Prospects.php           # pipeline de vendas
    Contracts.php           # emissão e envio de contratos
    ContractTemplates.php   # modelos reutilizáveis de contrato
  Client/
    Dashboard.php
    Projects.php
    Tasks.php               # approve / request-revision
    Support.php
    Financial.php
    Documents.php           # arquivos + contratos
    Contracts.php           # visualização e aceite digital

Models/
  UserModel.php
  ProjectModel.php
  TaskModel.php
  TicketModel.php / TicketMessageModel.php
  InvoiceModel.php
  DocumentModel.php
  ProspectModel.php
  ContractModel.php         # withClient() adiciona JOIN com users
  ContractTemplateModel.php

Views/
  layouts/admin.php         # sidebar admin (dark, Bootstrap)
  layouts/client.php        # sidebar cliente (dark navy, Bootstrap)
  layouts/auth.php          # tela de login/cadastro
  admin/prospects/          # index (pipeline), show (ficha)
  admin/contracts/          # index (tabela), show (editor Quill)
  admin/contract-templates/ # index (cards), show (editor Quill)
  client/documents.php      # tabs: Contratos | Arquivos
  client/contract-view.php  # leitura + botão aceite digital
```

---

## Rotas principais

```
/                           → login
/admin/*                    → AdminFilter (role=admin obrigatório)
/app/*                      → AuthFilter (qualquer login)
/api/contact/ticket         → público (POST do site WP)

# Admin — módulos
/admin/clients
/admin/projects
/admin/support
/admin/prospects            # pipeline de vendas
/admin/contracts            # contratos
/admin/contract-templates   # modelos de contrato

# Cliente
/app/documents              # tabs arquivos + contratos
/app/contracts/:id          # visualizar + aceitar contrato
```

---

## Banco de dados (tabelas)

| Tabela | Descrição |
|---|---|
| `users` | admins e clientes (`role`: admin/client) |
| `projects` | projetos vinculados a clientes |
| `project_tasks` | etapas com aprovação pelo cliente |
| `tickets` | chamados de suporte |
| `ticket_messages` | mensagens por chamado |
| `invoices` | faturas financeiras |
| `documents` | arquivos entregues (com `file_path`) |
| `notifications` | notificações do cliente |
| `prospects` | pipeline de vendas (6 status) |
| `contract_templates` | modelos de contrato (HTML, Quill) |
| `contracts` | contratos emitidos (aceite digital com IP) |

Schema completo em `db/init.sql`. Para aplicar em DB já rodando:
```bash
docker compose exec -T db psql -U painel_user -d painel_clientes < db/init.sql
```

---

## Módulos relevantes

### Pipeline de Vendas (`/admin/prospects`)
- 6 status: `new → contacted → qualified → proposal_sent → won / lost`
- Troca de status inline via AJAX (`fetch POST /admin/prospects/:id/status`)
- "Converter em cliente" cria user com role=client e senha temporária em flash separado (`temp_password`)

### Contratos (`/admin/contracts`)
- Status: `draft → sent → accepted → closed`
- Editor rich text: **Quill.js 1.3.7** via CDN
- Conteúdo stored como HTML na coluna `content`
- "Enviar ao cliente" muda `draft → sent`; cliente vê em Documentos
- Aceite registra `accepted_at` (timestamp) e `accepted_ip` (VARCHAR 45)
- Admin vê data/hora e IP do aceite na ficha do contrato

### Impersonation (admin entra como cliente)
- `GET /admin/clients/:id/login` → seta session com flag `impersonating`
- `GET /admin/stop-impersonating` → restaura session do admin (rota pública)

### Dark Mode (site WP)
- Ativado via `html[data-theme="dark"]`
- Anti-flash: script inline no `<head>` do `header.php` antes do CSS
- Preferência salva em `localStorage` (`gps-theme`)
- Overrides de estilos inline Gutenberg via seletores `[style*="background-color:#fff"]`

---

## Convenções de código CI4

- Controllers estendem `CodeIgniter\Controller` (não `BaseController`)
- Models usam `useTimestamps = true`, `allowedFields` explícito
- Validações manuais nos controllers (sem `$this->validate()`)
- Flash de sucesso: `->with('success', '...')` | Flash de erro: `->with('error', '...')`
- Flash especial `temp_password` usado para senhas temporárias (exibido em `<code>` no layout)
- Rotas sem `index.php` — `App.php` tem `$indexPage = ''`

---

## Site WordPress (`wordpress/wp-content/themes/astra-child/`)

- `functions.php` — enqueues, SEO hooks, Schema.org, constante `GPS_CLIENT_PORTAL_URL`
- `header.php` — header customizado com toggle dark/light, botão Login, hamburger
- `assets/css/header.css` — CSS completo do header + dark mode site todo
- `inc/rest-contact.php` — endpoint `POST /wp-json/gps/v1/contact` → repassa ao CI4
- `inc/contact-shortcode.php` — `[gps_contact_form tipo="geral|vendas|suporte"]`

URL do CI4 configurável via env var `CLIENT_PORTAL_URL` no `docker-compose.yml`.

---

## Comandos úteis

```powershell
# Subir ambiente
cd painel-clientes && docker compose up -d

# Logs
docker compose logs -f php
docker compose logs -f wordpress

# Acessar banco
docker compose exec db psql -U painel_user -d painel_clientes

# Rodar SQL avulso
docker compose exec -T db psql -U painel_user -d painel_clientes -c "SELECT * FROM users;"

# Reset total (apaga volumes)
docker compose down -v
```
