# Backup do PostgreSQL

O serviço `db_backup` (docker-compose) gera um dump `.sql.gz` a cada 24h em `./backups` e mantém os últimos 14 dias (`BACKUP_KEEP_DAYS`).
O primeiro dump sai logo que o serviço sobe. Copie a pasta `backups/` para fora da máquina (Drive, S3, HD externo) — backup só no mesmo disco não protege contra perda do computador/servidor.

Backup manual:

    docker compose exec -T db pg_dump --no-owner --clean --if-exists -U painel_user -d painel_clientes | gzip > backups/manual_$(date +%Y%m%d).sql.gz

Restaurar (apaga e recria os dados do banco indicado):

    gunzip -c backups/ARQUIVO.sql.gz | docker compose exec -T db psql -U painel_user -d painel_clientes
