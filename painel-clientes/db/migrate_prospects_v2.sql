-- Migração: enriquecimento da tabela prospects para captação via Google Maps
-- Rodar: docker compose exec -T db psql -U painel_user -d painel_clientes < db/migrate_prospects_v2.sql

-- Email passa a ser opcional (leads do Google Maps nem sempre têm email)
ALTER TABLE prospects ALTER COLUMN email DROP NOT NULL;

-- Novos campos
ALTER TABLE prospects
  ADD COLUMN IF NOT EXISTS rating        NUMERIC(2,1),
  ADD COLUMN IF NOT EXISTS reviews_count INTEGER,
  ADD COLUMN IF NOT EXISTS maps_url      VARCHAR(500);

-- Atualiza constraint de source para incluir 'google_maps'
DO $$
DECLARE
  v_name text;
BEGIN
  SELECT conname INTO v_name
  FROM pg_constraint
  WHERE conrelid = 'prospects'::regclass
    AND contype   = 'c'
    AND pg_get_constraintdef(oid) LIKE '%source%';
  IF v_name IS NOT NULL THEN
    EXECUTE 'ALTER TABLE prospects DROP CONSTRAINT ' || quote_ident(v_name);
  END IF;
END $$;

ALTER TABLE prospects ADD CONSTRAINT prospects_source_check
  CHECK (source IN ('website', 'referral', 'social', 'email', 'other', 'google_maps'));
