#!/usr/bin/env bash
# Exporta tudo o que o painel precisa para rodar em outra máquina num pacote só:
# banco (pg_dump), writable/uploads, writable/cnpj e os dois .env.
#
# Uso (na pasta painel-clientes, com o Docker do painel rodando):
#   bash scripts/migracao/exportar.sh              # sem a base do CNPJ (5 GB; baixe de novo na outra máquina)
#   bash scripts/migracao/exportar.sh --com-cnpj   # inclui writable/cnpj
#
# O pacote fica em backups/ (ignorado pelo git). Ele contém senhas: leve por pendrive ou nuvem
# pessoal, nunca pelo git nem por e-mail da empresa.
set -euo pipefail
export MSYS_NO_PATHCONV=1   # Git Bash no Windows: não converter caminhos do container

PROJ="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PROJ"

stamp="$(date +%Y%m%d-%H%M)"
work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT
dest="$work/painel-migracao-$stamp"
mkdir -p "$dest"

echo "== Conferindo o banco"
if ! docker compose exec -T db sh -c 'pg_isready -q -U "$POSTGRES_USER" -d "$POSTGRES_DB"'; then
    echo "O container do banco (db) não está respondendo. Suba o ambiente com: docker compose up -d" >&2
    exit 1
fi

echo "== Exportando o banco"
docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" --no-owner --clean --if-exists' \
    > "$dest/banco.sql"
grep -q 'PostgreSQL database dump complete' "$dest/banco.sql" || { echo "O dump do banco veio incompleto." >&2; exit 1; }

echo "== Contando os registros (para conferir na importação)"
docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -At -F "=" -c "
    SELECT '"'"'users'"'"', count(*) FROM users UNION ALL
    SELECT '"'"'prospects'"'"', count(*) FROM prospects UNION ALL
    SELECT '"'"'projects'"'"', count(*) FROM projects UNION ALL
    SELECT '"'"'tickets'"'"', count(*) FROM tickets UNION ALL
    SELECT '"'"'contracts'"'"', count(*) FROM contracts UNION ALL
    SELECT '"'"'invoices'"'"', count(*) FROM invoices UNION ALL
    SELECT '"'"'settings'"'"', count(*) FROM settings"' | tr -d '\r' > "$dest/contagens.txt"
cat "$dest/contagens.txt"

echo "== Copiando arquivos"
dirs="uploads"
if [ "${1:-}" = "--com-cnpj" ]; then
    dirs="uploads cnpj"
else
    echo "writable/cnpj: fora do pacote (base pública da Receita; use --com-cnpj para incluir)"
fi
for dir in $dirs; do
    if [ -d "painel/writable/$dir" ]; then
        mkdir -p "$dest/writable"
        cp -r "painel/writable/$dir" "$dest/writable/"
        echo "writable/$dir: $(find "$dest/writable/$dir" -type f | wc -l) arquivo(s)"
    fi
done
mkdir -p "$dest/env"
for f in .env painel/.env; do
    if [ -f "$f" ]; then
        cp "$f" "$dest/env/$(echo "$f" | tr '/' '_')"
        echo "$f: copiado"
    else
        echo "$f: não existe (será preciso criar na outra máquina a partir do .env.example)"
    fi
done

{
    echo "gerado_em=$(date '+%d/%m/%Y %H:%M')"
    echo "origem=$(hostname)"
    echo "commit=$(git rev-parse --short HEAD 2>/dev/null || echo desconhecido)"
} > "$dest/info.txt"

mkdir -p backups
pacote="backups/painel-migracao-$stamp.tar.gz"
tar -czf "$pacote" -C "$work" "painel-migracao-$stamp"

echo
echo "Pacote pronto: $PROJ/$pacote ($(du -h "$pacote" | cut -f1))"
echo "ATENÇÃO: o pacote tem as senhas dos .env. Leve por pendrive ou nuvem pessoal e apague depois de importar."
