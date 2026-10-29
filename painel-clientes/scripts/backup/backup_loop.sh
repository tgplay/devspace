#!/bin/sh
# Gera um dump a cada 24h e apaga os antigos. Roda dentro do container db_backup.
set -eu
KEEP="${BACKUP_KEEP_DAYS:-14}"
while true; do
  f="/backups/painel_$(date +%Y%m%d_%H%M%S).sql.gz"
  if pg_dump --no-owner --clean --if-exists > "$f.sql.tmp" && gzip -c "$f.sql.tmp" > "$f"; then echo "backup ok: $f"; else rm -f "$f"; echo "backup FALHOU" >&2; fi
  rm -f "$f.sql.tmp"
  find /backups -name "painel_*.sql.gz" -mtime +"$KEEP" -delete
  sleep 86400
done
