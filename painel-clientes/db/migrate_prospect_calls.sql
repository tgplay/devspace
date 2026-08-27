-- Migração: follow-up por telefone para prospects sem WhatsApp
-- Rodar: docker compose exec -T db psql -U painel_user -d painel_clientes < db/migrate_prospect_calls.sql

-- Marca quando o prospect entrou na lista de follow-up por telefone
ALTER TABLE prospects
  ADD COLUMN IF NOT EXISTS phone_followup_at TIMESTAMP;

-- Atualiza constraint de status para incluir 'phone_followup'
DO $$
DECLARE
  v_name text;
BEGIN
  SELECT conname INTO v_name
  FROM pg_constraint
  WHERE conrelid = 'prospects'::regclass
    AND contype   = 'c'
    AND pg_get_constraintdef(oid) LIKE '%status%'
    AND pg_get_constraintdef(oid) LIKE '%new%';
  IF v_name IS NOT NULL THEN
    EXECUTE 'ALTER TABLE prospects DROP CONSTRAINT ' || quote_ident(v_name);
  END IF;
END $$;

ALTER TABLE prospects ADD CONSTRAINT prospects_status_check
  CHECK (status IN ('new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost', 'phone_followup'));

-- Histórico de ligações registradas por prospect
CREATE TABLE IF NOT EXISTS prospect_calls (
    id          SERIAL PRIMARY KEY,
    prospect_id INTEGER   NOT NULL REFERENCES prospects(id) ON DELETE CASCADE,
    notes       TEXT,
    created_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_prospect_calls_prospect ON prospect_calls(prospect_id);
