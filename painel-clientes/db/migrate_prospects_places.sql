-- Migração: conformidade com os termos do Google Places (só o place_id é armazenado)
-- Rodar: Get-Content db\migrate_prospects_places.sql -Raw | docker compose exec -T db psql -U painel_user -d painel_clientes
-- Idempotente.

ALTER TABLE prospects
  ADD COLUMN IF NOT EXISTS google_place_id    VARCHAR(255),
  ADD COLUMN IF NOT EXISTS places_released_at TIMESTAMP;

-- Um lugar do Google só pode virar prospecto uma vez
CREATE UNIQUE INDEX IF NOT EXISTS uq_prospects_google_place_id
  ON prospects(google_place_id) WHERE google_place_id IS NOT NULL;

COMMENT ON COLUMN prospects.google_place_id IS 'Único dado do Google Places armazenado (permitido pelos termos). Nome/telefone/nota são buscados ao vivo.';
COMMENT ON COLUMN prospects.places_released_at IS 'Quando o operador salvou nome/telefone após a pessoa responder: a partir daí são dados do relacionamento, não cache do Google.';
