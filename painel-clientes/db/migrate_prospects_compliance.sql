-- Migração: conformidade LGPD da prospecção (opt-out, base legal, origem, retenção)
-- Rodar: docker compose exec -T db psql -U painel_user -d painel_clientes < db/migrate_prospects_compliance.sql
-- Idempotente: pode ser executada mais de uma vez.

ALTER TABLE prospects
  ADD COLUMN IF NOT EXISTS opt_out_at       TIMESTAMP,
  ADD COLUMN IF NOT EXISTS legal_basis      VARCHAR(40) NOT NULL DEFAULT 'legitimate_interest',
  ADD COLUMN IF NOT EXISTS contact_source   VARCHAR(255),
  ADD COLUMN IF NOT EXISTS first_contact_at TIMESTAMP,
  ADD COLUMN IF NOT EXISTS delete_after     DATE DEFAULT (CURRENT_DATE + 365);

-- Valores aceitos para a base legal (art. 7º LGPD). Padrão: legítimo interesse (B2B) — exige LIA documentada.
ALTER TABLE prospects DROP CONSTRAINT IF EXISTS prospects_legal_basis_check;
ALTER TABLE prospects ADD CONSTRAINT prospects_legal_basis_check
  CHECK (legal_basis IN ('legitimate_interest', 'consent', 'contract_preparation'));

-- Backfill dos registros existentes
UPDATE prospects
   SET contact_source = 'Google Maps (dados públicos do estabelecimento)'
 WHERE contact_source IS NULL AND source = 'google_maps';

UPDATE prospects
   SET delete_after = (created_at::date + 365)
 WHERE delete_after IS NULL AND status <> 'won';

CREATE INDEX IF NOT EXISTS idx_prospects_opt_out     ON prospects(opt_out_at) WHERE opt_out_at IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_prospects_delete_after ON prospects(delete_after);
