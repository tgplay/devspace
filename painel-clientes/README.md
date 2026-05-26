# Painel de Clientes

Sistema web para gerenciamento de projetos, suporte e financeiro entre agência e clientes.

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2 + CodeIgniter 4.7 |
| Banco de dados | PostgreSQL 15 |
| Servidor web | Nginx |
| Ambiente | Docker + Docker Compose |
| Frontend | Bootstrap 5.3 + Bootstrap Icons |

---

## Estrutura do projeto

```
painel-clientes/
├── docker-compose.yml       # Orquestra os 3 serviços (Nginx, PHP, PostgreSQL)
├── .env                     # Credenciais do banco para o Docker Compose
│
├── nginx/conf.d/
│   ├── admin.conf           # localhost:8080 → painel do admin
│   └── app.conf             # localhost:8081 → painel do cliente
│
├── php/
│   └── Dockerfile           # PHP 8.2-FPM com extensões pdo_pgsql, intl, zip
│
├── db/
│   └── init.sql             # Schema completo do banco (criado automaticamente)
│
└── painel/                  # Aplicação CodeIgniter 4
    ├── app/
    │   ├── Config/
    │   │   ├── Routes.php   # Todas as rotas da aplicação
    │   │   └── Filters.php  # Registro dos filtros de autenticação
    │   ├── Controllers/
    │   │   ├── Auth.php     # Login, logout e cadastro
    │   │   ├── Admin/       # Controllers do painel admin
    │   │   └── Client/      # Controllers do painel do cliente
    │   ├── Filters/
    │   │   ├── AuthFilter.php   # Bloqueia rotas /app/* se não logado
    │   │   └── AdminFilter.php  # Bloqueia rotas /admin/* se não for admin
    │   ├── Models/          # Um model por tabela do banco
    │   └── Views/
    │       ├── layouts/     # Templates base (auth, admin, client)
    │       ├── auth/        # Telas de login e cadastro
    │       ├── admin/       # Telas do painel admin
    │       └── client/      # Telas do painel do cliente
    └── .env                 # Configuração do CodeIgniter (DB, sessão, etc.)
```

---

## Como rodar

### Pré-requisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e rodando
- WSL2 habilitado (Windows)

### 1. Instalar dependências PHP (apenas na primeira vez)

```powershell
docker run --rm -v "C:/caminho/para/painel-clientes/painel:/app" -w /app composer:2 install --no-dev --ignore-platform-req=ext-intl
```

> O flag `--ignore-platform-req=ext-intl` é obrigatório porque o container do Composer não tem a extensão `intl` — mas o container PHP do projeto tem. Sem esse flag o Composer recusa instalar.

### 2. Subir os containers

```powershell
cd painel-clientes
docker-compose up -d --build
```

### 3. Acessar

| URL | O que é |
|---|---|
| http://localhost:8080 | Painel Admin |
| http://localhost:8081 | Dashboard do Cliente |
| localhost:5433 | PostgreSQL (acesso direto, ex: DBeaver) |

---

## Acessos padrão

| Perfil | E-mail | Senha |
|---|---|---|
| Admin | admin@painel.local | admin123 |

> O admin padrão é criado automaticamente pelo `db/init.sql` na primeira vez que o banco sobe.
> **Troque a senha após o primeiro acesso.**

Clientes se cadastram em `http://localhost:8080/register`.

---

## Como funciona o controle de acesso

O CodeIgniter usa **Filtros** para proteger as rotas:

- Rotas `/admin/*` → `AdminFilter` — exige login **e** perfil `admin`
- Rotas `/app/*` → `AuthFilter` — exige apenas login (qualquer perfil)
- O admin pode entrar no painel de qualquer cliente via **"Entrar como cliente"** na tela de detalhes do cliente

---

## Banco de dados

| Tabela | O que armazena |
|---|---|
| `users` | Admins e clientes (campo `role`: `admin` ou `client`) |
| `projects` | Projetos vinculados a um cliente |
| `project_tasks` | Etapas de cada projeto (com aprovação pelo cliente) |
| `tickets` | Chamados de suporte |
| `ticket_messages` | Mensagens de cada chamado |
| `invoices` | Faturas financeiras |
| `documents` | Arquivos e documentos entregues |
| `notifications` | Notificações do cliente |

O banco é criado automaticamente na primeira vez que o container `db` sobe, executando `db/init.sql`.

---

## Solução de problemas

### Redefinir senha do admin

Se o login do admin não funcionar, crie o arquivo `painel/update_admin.php` com o conteúdo abaixo, execute e depois delete:

```php
<?php
$db   = new PDO('pgsql:host=db;dbname=painel_clientes', 'painel_user', 'painel_pass');
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $db->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->execute([$hash, 'admin@painel.local']);
echo 'Senha atualizada!' . PHP_EOL;
```

```powershell
# Executar o script dentro do container
docker-compose exec php php /var/www/painel/update_admin.php

# Depois deletar
Remove-Item painel\update_admin.php
```

> **Por que isso é necessário?** O `init.sql` usa `crypt()` do pgcrypto para gerar o hash no PostgreSQL. Em bancos já existentes (criados antes dessa correção) o hash pode estar incompatível com o `password_verify()` do PHP. O script acima regera o hash diretamente via PHP, garantindo compatibilidade.

---

## Comandos úteis

```powershell
# Ver logs em tempo real
docker-compose logs -f

# Parar tudo
docker-compose down

# Parar e apagar o banco (reset total)
docker-compose down -v

# Acessar o container PHP
docker-compose exec php sh

# Acessar o PostgreSQL direto
docker-compose exec db psql -U painel_user -d painel_clientes
```
