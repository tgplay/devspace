# Painel de Clientes + Site devspace

Monorepo com dois sistemas interdependentes:

| Sistema | Função | URL local |
|---|---|---|
| **Painel de Clientes** (CI4) | Gestão interna de projetos, tickets e financeiro | `localhost:8080` (admin) / `localhost:8081` (cliente) |
| **Site devspace** (WordPress) | Site institucional com formulário de contato integrado ao painel | `localhost:8082` |

Ambos rodam no mesmo `docker-compose.yml` e se comunicam pela rede interna Docker.

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend painel | PHP 8.2 + CodeIgniter 4.7 |
| Banco painel | PostgreSQL 15 |
| CMS site | WordPress 7.0 + tema Astra Child |
| Banco WordPress | MySQL 8.0 |
| Servidor web | Nginx |
| Ambiente | Docker + Docker Compose |
| Frontend painel | Bootstrap 5.3 + Bootstrap Icons |
| Frontend site | Gutenberg (editor nativo WP) + CSS customizado |

---

## Estrutura do projeto

```
painel-clientes/
│
├── docker-compose.yml           # Orquestra todos os serviços
├── .env                         # Credenciais do banco PostgreSQL
│
├── nginx/
│   └── conf.d/
│       ├── admin.conf           # localhost:8080 → painel admin (CI4)
│       ├── app.conf             # localhost:8081 → painel cliente (CI4)
│       └── wordpress.conf       # localhost:8082 → site devspace (WP)
│
├── php/
│   └── Dockerfile               # PHP 8.2-FPM com pdo_pgsql, intl, zip
│
├── db/
│   └── init.sql                 # Schema PostgreSQL (auto-executado na 1ª subida)
│
├── painel/                      # Aplicação CodeIgniter 4
│   ├── app/
│   │   ├── Config/
│   │   │   ├── Routes.php       # Todas as rotas (inclui /api/contact/ticket)
│   │   │   └── Filters.php      # Filtros de autenticação
│   │   ├── Controllers/
│   │   │   ├── Auth.php         # Login, logout, cadastro
│   │   │   ├── Api/
│   │   │   │   └── Contact.php  # Endpoint público recebe formulário do site WP
│   │   │   ├── Admin/           # Controllers do painel admin
│   │   │   └── Client/          # Controllers do painel do cliente
│   │   ├── Filters/
│   │   │   ├── AuthFilter.php   # Protege rotas /app/*
│   │   │   └── AdminFilter.php  # Protege rotas /admin/*
│   │   ├── Models/              # Um model por tabela
│   │   └── Views/
│   │       ├── layouts/         # Templates base (auth, admin, client)
│   │       ├── auth/            # Login e cadastro
│   │       ├── admin/           # Telas do admin
│   │       └── client/          # Telas do cliente
│   └── .env                     # Config CI4 (DB, sessão, base URL)
│
└── wordpress/                   # Site devspace (WordPress)
    └── wp-content/
        └── themes/
            └── astra-child/     # Tema filho do Astra (tema principal do site)
                ├── style.css    # Declaração do tema filho
                ├── functions.php
                ├── header.php
                ├── assets/
                │   ├── css/
                │   │   └── header.css
                │   └── images/  # og-image.jpg e logo.png ficam aqui
                └── inc/
                    ├── rest-contact.php       # Endpoint WP → CI4
                    ├── contact-shortcode.php  # [gps_contact_form]
                    ├── cookie-banner.php      # Banner LGPD
                    ├── home-page-content.html
                    ├── servicos-page-content.html
                    ├── portfolio-page-content.html
                    └── contato-page-content.html
```

---

## Como rodar

### Pré-requisitos
- Docker Desktop instalado e rodando
- WSL2 habilitado (Windows)

### 1. Instalar dependências PHP do painel (apenas na primeira vez)

```powershell
docker run --rm -v "C:/caminho/para/painel-clientes/painel:/app" -w /app composer:2 install --no-dev --ignore-platform-req=ext-intl
```

> `--ignore-platform-req=ext-intl` é obrigatório: o container do Composer não tem `intl`, mas o container PHP do projeto tem.

### 2. Subir os containers

```powershell
cd painel-clientes
docker-compose up -d --build
```

### 3. Aguardar o WordPress inicializar

Na primeira subida, o container `wordpress` extrai os arquivos do WP em `./wordpress/`. Isso pode levar **1–2 minutos** em WSL2. Antes disso, `localhost:8082` retorna 403.

### 4. Configurar o WordPress (apenas na primeira vez)

Acesse `http://localhost:8082/wp-admin/install.php` e conclua o wizard de instalação. Depois:

- **Aparência → Temas → Ativar** o tema "Astra Child — GPS Vista"
- Instalar o plugin **Astra** (tema pai) via Plugins → Adicionar novo
- Criar as páginas via WP-CLI (ver seção abaixo) ou manualmente

### 5. Acessar

| URL | O que é |
|---|---|
| `http://localhost:8080` | Painel Admin (CI4) |
| `http://localhost:8081` | Painel do Cliente (CI4) |
| `http://localhost:8082` | Site devspace (WordPress) |
| `localhost:5433` | PostgreSQL (ex: DBeaver) |

---

## Acessos padrão

| Perfil | E-mail | Senha |
|---|---|---|
| Admin CI4 | admin@painel.local | admin123 |

> O admin é criado automaticamente pelo `db/init.sql` na primeira subida.
> **Troque a senha após o primeiro acesso.**

---

## Site devspace (WordPress)

### Visão geral

Site institucional da devspace com 4 páginas principais, integração de formulário de contato com o painel CI4, banner de consentimento de cookies (LGPD) e SEO completo implementado no tema.

### Páginas

| Slug | Título | Descrição |
|---|---|---|
| `/` | Home | Hero escuro, seção de serviços, diferenciais, portfólio em destaque, CTA |
| `/servicos` | Serviços | Seções Sites, Apps e Sistemas com timeline "Como trabalhamos" |
| `/portfolio` | Portfólio | Grid filtrável (Todos / Sites / Apps / Sistemas) com 6 projetos mockados |
| `/contato` | Contato | Cards de canais de atendimento + formulário integrado ao CI4 |

### Tema Astra Child

Arquivo | Função
---|---
`style.css` | Declara o tema filho (`Template: astra`)
`functions.php` | Enqueue de estilos, SEO hooks, Schema.org, banner de cookies, includes
`header.php` | Header customizado: logo, menu principal, botão Login, dropdown Atendimento, hamburger mobile
`assets/css/header.css` | CSS completo do header (sticky, dropdown CSS-only, botões, mobile)
`inc/rest-contact.php` | Registra `POST /wp-json/gps/v1/contact` — recebe e repassa ao CI4
`inc/contact-shortcode.php` | Shortcode `[gps_contact_form tipo="geral\|vendas\|suporte"]`
`inc/cookie-banner.php` | Banner LGPD fixo no rodapé com aceite via localStorage
`inc/home-page-content.html` | Conteúdo Gutenberg da Home
`inc/servicos-page-content.html` | Conteúdo Gutenberg da página Serviços
`inc/portfolio-page-content.html` | Conteúdo Gutenberg da página Portfólio
`inc/contato-page-content.html` | Conteúdo Gutenberg da página Contato

### Fluxo do formulário de contato

```
Visitante preenche [gps_contact_form]
        │
        ▼
fetch POST /wp-json/gps/v1/contact   (WordPress REST API)
        │
        ▼
inc/rest-contact.php sanitiza e repassa via wp_remote_post
        │
        ▼
http://nginx:8080/api/contact/ticket   (rede interna Docker)
        │
        ▼
Api\Contact::ticket() no CI4
  ├── cria ou localiza usuário na tabela `users`
  ├── cria ticket na tabela `tickets`
  └── cria mensagem inicial em `ticket_messages`
```

O CI4 responde com JSON `{ success: true, message: "..." }` que o JS do formulário exibe ao usuário.

### Header

- **Logo**: exibe custom logo cadastrado no WP Admin → Personalizar; fallback para nome do site
- **Menu principal**: registrado como `primary` — gerenciar em WP Admin → Aparência → Menus
- **Botão "Login"**: aponta para `/login` no CI4; após autenticação o CI4 redireciona para o dashboard conforme o papel (cliente → `/app`, admin → `/admin`)
- **Dropdown "Atendimento"**: CSS-only (`:hover` + `:focus-within`), sem JS — links para `/contato?tipo=vendas` e `/contato?tipo=suporte`
- **Hamburger**: JS inline mínimo em `header.php`, toggling classe `.open` no nav

### SEO implementado

Tudo está em `functions.php`, sem dependência de plugin.

| Recurso | Implementação |
|---|---|
| `<title>` dinâmico por página | `add_theme_support('title-tag')` + `document_title_parts` filter |
| `<meta name="description">` | Descrição única por página via `wp_head` hook (prioridade 1) |
| `<link rel="canonical">` | Gerado automaticamente por página |
| Open Graph (`og:*`) | `og:type`, `og:title`, `og:description`, `og:url`, `og:image`, `og:locale` |
| Twitter Card | `summary_large_image` |
| Schema.org `Organization` | JSON-LD injetado em todas as páginas |
| Schema.org `WebSite` | Na home, com `SearchAction` |
| Schema.org `ItemList` | Na página Serviços (Sites, Apps, Sistemas) |
| Schema.org `ContactPage` | Na página Contato |
| Alt text do logo | Filter `get_custom_logo` |
| `robots.txt` | Criado em `wordpress/robots.txt` |

**Antes de ir ao ar:**
- Adicionar `assets/images/og-image.jpg` (1200×630 px) para preview em redes sociais
- Adicionar `assets/images/logo.png` para o Schema.org
- Atualizar o número de telefone em `functions.php` (linha com `+55-11-99999-9999`)
- Atualizar a URL do `Sitemap:` em `robots.txt` com o domínio real
- Submeter o site no Google Search Console após o deploy

### Banner de cookies (LGPD)

Implementado em `inc/cookie-banner.php`, injetado via `wp_footer`. Exibe faixa escura no rodapé com botão "Entendi". A aceitação é salva em `localStorage` (`dv_cookies_ok = 1`) e o banner não reaparece.

---

## Painel de Clientes (CodeIgniter 4)

### Controle de acesso

- Rotas `/admin/*` → `AdminFilter` — exige login **e** perfil `admin`
- Rotas `/app/*` → `AuthFilter` — exige login (qualquer perfil)
- O admin pode entrar no painel de qualquer cliente via **"Entrar como cliente"**
- CSRF desabilitado globalmente em `Filters.php` para permitir o endpoint público `/api/contact/ticket`

### Banco de dados (PostgreSQL)

| Tabela | O que armazena |
|---|---|
| `users` | Admins e clientes (`role`: `admin` ou `client`) |
| `projects` | Projetos vinculados a clientes |
| `project_tasks` | Etapas com aprovação pelo cliente |
| `tickets` | Chamados de suporte |
| `ticket_messages` | Mensagens de cada chamado |
| `invoices` | Faturas financeiras |
| `documents` | Arquivos e documentos entregues |
| `notifications` | Notificações do cliente |

Criado automaticamente na primeira subida via `db/init.sql`.

### Endpoint público `/api/contact/ticket`

Recebe dados do formulário WordPress e:
1. Busca ou cria o usuário pelo e-mail (papel `client`, conta inativa)
2. Cria um ticket com assunto prefixado conforme o tipo (`[Orçamento]`, `[Suporte]`, `[Contato]`)
3. Cria a mensagem inicial do ticket

Usa `INSERT ... RETURNING id` (PostgreSQL) para obter o ID de forma confiável.

---

## Solução de problemas

### `localhost:8082` retorna 403 Forbidden

O container `wordpress` ainda está extraindo os arquivos. Aguarde 1–2 minutos e recarregue.

### Redefinir senha do admin CI4

```php
// painel/update_admin.php (deletar após executar)
<?php
$db   = new PDO('pgsql:host=db;dbname=painel_clientes', 'painel_user', 'painel_pass');
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->execute([$hash, 'admin@painel.local']);
echo 'Senha atualizada!' . PHP_EOL;
```

```powershell
docker-compose exec php php /var/www/painel/update_admin.php
Remove-Item painel\update_admin.php
```

### WP-CLI dentro do container

```bash
# Prefixo obrigatório no Git Bash/Windows para evitar conversão de paths
MSYS_NO_PATHCONV=1 docker exec painel-clientes-wordpress-1 \
  wp --path=/var/www/html --allow-root <comando>
```

---

## Comandos úteis

```powershell
# Ver logs em tempo real
docker-compose logs -f

# Ver logs só do WordPress
docker-compose logs -f wordpress

# Parar tudo
docker-compose down

# Parar e apagar bancos (reset total — MySQL e PostgreSQL)
docker-compose down -v

# Acessar container PHP (CI4)
docker-compose exec php sh

# Acessar container WordPress
docker-compose exec wordpress sh

# Acessar PostgreSQL direto
docker-compose exec db psql -U painel_user -d painel_clientes

# Reinstalar dependências CI4
docker run --rm -v "C:/caminho/painel:/app" -w /app composer:2 install --no-dev --ignore-platform-req=ext-intl
```
