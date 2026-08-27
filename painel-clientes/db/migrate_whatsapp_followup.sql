-- Migração: follow-up para prospects contatados via WhatsApp sem retorno confirmado
-- Rodar: docker compose exec -T db psql -U painel_user -d painel_clientes < db/migrate_whatsapp_followup.sql

-- Marca quando o prospect entrou na lista de follow-up de WhatsApp
ALTER TABLE prospects
  ADD COLUMN IF NOT EXISTS whatsapp_followup_at TIMESTAMP;

-- Atualiza constraint de status para incluir 'whatsapp_followup'
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
  CHECK (status IN ('new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost', 'phone_followup', 'whatsapp_followup'));
