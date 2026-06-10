-- ============================================================
-- Schema: painel_clientes
-- ============================================================

-- Extensão para UUID
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- Usuários (admins e clientes)
-- ============================================================
CREATE TABLE users (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(120)        NOT NULL,
    email       VARCHAR(180)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    role        VARCHAR(20)         NOT NULL DEFAULT 'client' CHECK (role IN ('admin', 'client')),
    phone       VARCHAR(30),
    avatar_url  VARCHAR(500),
    active      BOOLEAN             NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP           NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP           NOT NULL DEFAULT NOW()
);

-- Admin padrão (senha: admin123 — trocar em produção)
-- crypt() do pgcrypto gera hash bcrypt compatível com password_verify() do PHP
INSERT INTO users (name, email, password, role)
VALUES ('Administrador', 'admin@painel.local', crypt('admin123', gen_salt('bf', 10)), 'admin');

-- ============================================================
-- Projetos
-- ============================================================
CREATE TABLE projects (
    id           SERIAL PRIMARY KEY,
    client_id    INTEGER             NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name         VARCHAR(200)        NOT NULL,
    description  TEXT,
    type         VARCHAR(30)         NOT NULL CHECK (type IN ('site', 'app', 'system', 'other')),
    status       VARCHAR(30)         NOT NULL DEFAULT 'planning'
                    CHECK (status IN ('planning', 'development', 'review', 'delivered', 'maintenance')),
    progress     SMALLINT            NOT NULL DEFAULT 0 CHECK (progress BETWEEN 0 AND 100),
    deadline     DATE,
    delivery_url VARCHAR(500),
    created_at   TIMESTAMP           NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP           NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Etapas / Tarefas do projeto
-- ============================================================
CREATE TABLE project_tasks (
    id                SERIAL PRIMARY KEY,
    project_id        INTEGER         NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    title             VARCHAR(200)    NOT NULL,
    description       TEXT,
    status            VARCHAR(30)     NOT NULL DEFAULT 'pending'
                        CHECK (status IN ('pending', 'done', 'awaiting_approval', 'revision_requested')),
    requires_approval BOOLEAN         NOT NULL DEFAULT FALSE,
    approved_at       TIMESTAMP,
    approved_by       INTEGER         REFERENCES users(id),
    sort_order        SMALLINT        NOT NULL DEFAULT 0,
    created_at        TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at        TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Suporte — Chamados
-- ============================================================
CREATE TABLE tickets (
    id         SERIAL PRIMARY KEY,
    client_id  INTEGER         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    subject    VARCHAR(200)    NOT NULL,
    status     VARCHAR(30)     NOT NULL DEFAULT 'open'
                 CHECK (status IN ('open', 'attending', 'resolved', 'closed')),
    priority   VARCHAR(20)     NOT NULL DEFAULT 'normal'
                 CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    created_at TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP       NOT NULL DEFAULT NOW()
);

CREATE TABLE ticket_messages (
    id         SERIAL PRIMARY KEY,
    ticket_id  INTEGER         NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    sender_id  INTEGER         NOT NULL REFERENCES users(id),
    message    TEXT            NOT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Financeiro — Faturas
-- ============================================================
CREATE TABLE invoices (
    id          SERIAL PRIMARY KEY,
    client_id   INTEGER         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id  INTEGER         REFERENCES projects(id) ON DELETE SET NULL,
    description VARCHAR(300)    NOT NULL,
    amount      NUMERIC(12,2)   NOT NULL,
    status      VARCHAR(20)     NOT NULL DEFAULT 'pending'
                  CHECK (status IN ('pending', 'paid', 'overdue', 'cancelled')),
    due_date    DATE            NOT NULL,
    paid_at     TIMESTAMP,
    boleto_url  VARCHAR(500),
    nf_url      VARCHAR(500),
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Documentos
-- ============================================================
CREATE TABLE documents (
    id          SERIAL PRIMARY KEY,
    client_id   INTEGER         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id  INTEGER         REFERENCES projects(id) ON DELETE SET NULL,
    name        VARCHAR(200)    NOT NULL,
    type        VARCHAR(30)     NOT NULL DEFAULT 'other'
                  CHECK (type IN ('contract', 'briefing', 'delivery', 'other')),
    file_path   VARCHAR(500)    NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Notificações
-- ============================================================
CREATE TABLE notifications (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title      VARCHAR(200)    NOT NULL,
    message    TEXT            NOT NULL,
    link       VARCHAR(500),
    read_at    TIMESTAMP,
    created_at TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Prospectos (pipeline de vendas)
-- ============================================================
CREATE TABLE prospects (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(200)        NOT NULL,
    email       VARCHAR(180)        NOT NULL,
    phone       VARCHAR(30),
    company     VARCHAR(200),
    interest    VARCHAR(30)         NOT NULL DEFAULT 'other'
                  CHECK (interest IN ('site', 'app', 'system', 'other')),
    source      VARCHAR(30)         NOT NULL DEFAULT 'website'
                  CHECK (source IN ('website', 'referral', 'social', 'email', 'other')),
    status      VARCHAR(30)         NOT NULL DEFAULT 'new'
                  CHECK (status IN ('new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost')),
    notes       TEXT,
    created_at  TIMESTAMP           NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP           NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Modelos de contrato
-- ============================================================
CREATE TABLE contract_templates (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    content     TEXT            NOT NULL DEFAULT '',
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Contratos (gerados pelo admin, aceitos pelo cliente)
-- ============================================================
CREATE TABLE contracts (
    id          SERIAL PRIMARY KEY,
    client_id   INTEGER         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id  INTEGER         REFERENCES projects(id) ON DELETE SET NULL,
    template_id INTEGER         REFERENCES contract_templates(id) ON DELETE SET NULL,
    title       VARCHAR(200)    NOT NULL,
    content     TEXT            NOT NULL DEFAULT '',
    value       NUMERIC(12,2),
    start_date  DATE,
    end_date    DATE,
    status      VARCHAR(20)     NOT NULL DEFAULT 'draft'
                  CHECK (status IN ('draft', 'sent', 'accepted', 'closed')),
    accepted_at TIMESTAMP,
    accepted_ip VARCHAR(45),
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);

-- ============================================================
-- Índices
-- ============================================================
CREATE INDEX idx_projects_client    ON projects(client_id);
CREATE INDEX idx_tasks_project      ON project_tasks(project_id);
CREATE INDEX idx_tickets_client     ON tickets(client_id);
CREATE INDEX idx_ticket_msgs_ticket ON ticket_messages(ticket_id);
CREATE INDEX idx_invoices_client    ON invoices(client_id);
CREATE INDEX idx_documents_client   ON documents(client_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_prospects_status   ON prospects(status);
CREATE INDEX idx_prospects_created  ON prospects(created_at);
CREATE INDEX idx_contracts_client   ON contracts(client_id);
CREATE INDEX idx_contracts_status   ON contracts(status);
