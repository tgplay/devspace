#!/usr/bin/env bash
# Importa um pacote gerado pelo exportar.sh: .env, arquivos e banco. Sobe o Docker do painel.
#
# Uso (na pasta painel-clientes da máquina nova):
#   bash scripts/migracao/importar.sh ~/Downloads/painel-migracao-AAAAMMDD-HHMM.tar.gz
#   bash scripts/migracao/importar.sh <pacote> --forcar    # substitui um banco que já tem dados
set -euo pipefail
export MSYS_NO_PATHCONV=1

pacote="${1:-}"
forcar="${2:-}"
if [ -z "$pacote" ] || [ ! -f "$pacote" ]; then
    echo "Informe o pacote: bash scripts/migracao/importar.sh <painel-migracao-....tar.gz>" >&2
    exit 1
fi
pacote="$(cd "$(dirname "$pacote")" && pwd)/$(basename "$pacote")"

PROJ="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PROJ"

work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT
tar -xzf "$pacote" -C "$work"
src="$(find "$work" -maxdepth 1 -type d -name 'painel-migracao-*' | head -1)"
[ -f "$src/banco.sql" ] || { echo "Pacote inválido: falta banco.sql." >&2; exit 1; }
echo "== Pacote: $(tr '\n' ' ' < "$src/info.txt")"

echo "== Arquivos .env"
# Um .env que já existe aqui fica como está: o volume do banco pode ter sido criado com ele.
for par in ".env:.env" "painel_.env:painel/.env"; do
    origem="$src/env/${par%%:*}"; destino="${par#*:}"
    if [ -f "$destino" ]; then
        echo "$destino: já existe, mantido"
    elif [ -f "$origem" ]; then
        cp "$origem" "$destino"; echo "$destino: copiado do pacote"
    else
        echo "$destino: não veio no pacote; crie a partir do .env.example antes de continuar" >&2; exit 1
    fi
done

echo "== Arquivos (writable)"
if [ -d "$src/writable" ]; then
    for dir in "$src"/writable/*/; do
        nome="$(basename "$dir")"
        mkdir -p "painel/writable/$nome"
        cp -r "$dir". "painel/writable/$nome/"
        echo "writable/$nome: $(find "painel/writable/$nome" -type f | wc -l) arquivo(s)"
    done
fi

echo "== Subindo o Docker (a primeira vez demora: baixa as imagens)"
docker compose up -d --build

echo "== Esperando o banco"
for i in $(seq 1 60); do
    if docker compose exec -T db sh -c 'pg_isready -q -U "$POSTGRES_USER" -d "$POSTGRES_DB"' 2>/dev/null; then break; fi
    [ "$i" = 60 ] && { echo "O banco não respondeu em 2 minutos. Veja: docker compose logs db" >&2; exit 1; }
    sleep 2
done

if [ ! -f painel/vendor/autoload.php ]; then
    echo "== Instalando as bibliotecas PHP (composer)"
    docker compose exec -T php composer install --no-interaction --no-progress
fi

echo "== Conferindo se o banco daqui já tem dados"
psql_aqui() { docker compose exec -T db sh -c "psql -U \"\$POSTGRES_USER\" -d \"\$POSTGRES_DB\" -At $*"; }
existentes="$(psql_aqui -c "\"SELECT (SELECT count(*) FROM prospects) + (SELECT count(*) FROM projects) + (SELECT count(*) FROM tickets) + GREATEST((SELECT count(*) FROM users) - 1, 0)\"" 2>/dev/null | tr -d '\r' || echo 0)"
if [ "${existentes:-0}" != "0" ] && [ "$forcar" != "--forcar" ]; then
    echo "Este banco já tem $existentes registro(s) (prospects, projetos, chamados ou usuários além do admin)." >&2
    echo "A importação APAGA esses dados e põe os do pacote no lugar. Para seguir: rode de novo com --forcar" >&2
    exit 1
fi

echo "== Importando o banco"
docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -q -v ON_ERROR_STOP=1' < "$src/banco.sql" > /dev/null

echo "== Conferindo as contagens"
falhou=0
while IFS='=' read -r tabela esperado; do
    [ -z "$tabela" ] && continue
    obtido="$(psql_aqui -c "\"SELECT count(*) FROM $tabela\"" | tr -d '\r')"
    if [ "$obtido" = "$esperado" ]; then echo "ok   $tabela: $obtido"; else echo "ERRO $tabela: esperado $esperado, veio $obtido"; falhou=1; fi
done < "$src/contagens.txt"
[ "$falhou" = 0 ] || { echo "As contagens não bateram. Não apague o pacote." >&2; exit 1; }

echo
echo "Importação concluída. Admin: http://localhost:8080/login   Cliente: http://localhost:8081/login"
echo "Depois de conferir o painel, apague o pacote: ele tem as senhas dos .env."
