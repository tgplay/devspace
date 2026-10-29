-- Migração: importação de leads pela base aberta de CNPJ (Receita Federal)
-- Rodar: Get-Content db\migrate_prospects_cnpj.sql -Raw | docker compose exec -T db psql -U painel_user -d painel_clientes
-- Idempotente.

ALTER TABLE prospects ADD COLUMN IF NOT EXISTS cnpj VARCHAR(14);

CREATE UNIQUE INDEX IF NOT EXISTS uq_prospects_cnpj ON prospects(cnpj) WHERE cnpj IS NOT NULL;

-- Novo valor de origem: 'cnpj'
DO $$
DECLARE
  v_name text;
BEGIN
  FOR v_name IN
    SELECT conname FROM pg_constraint
    WHERE conrelid = 'prospects'::regclass AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%source%'
  LOOP
    EXECUTE 'ALTER TABLE prospects DROP CONSTRAINT ' || quote_ident(v_name);
  END LOOP;
END $$;

ALTER TABLE prospects ADD CONSTRAINT prospects_source_check
  CHECK (source IN ('website', 'referral', 'social', 'email', 'other', 'google_maps', 'cnpj'));
