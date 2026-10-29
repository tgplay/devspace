# Migração do painel para outra máquina

Leva o painel de uma máquina para outra **sem perder os dados**: banco (prospects, clientes, projetos,
chamados, contratos, configurações), arquivos enviados (`writable/uploads`) e os dois `.env`.

| script | onde roda | o que faz |
|---|---|---|
| `exportar.sh` | máquina de origem | gera `backups/painel-migracao-AAAAMMDD-HHMM.tar.gz` com o banco, os uploads, os `.env` e a contagem de registros |
| `importar.sh` | máquina de destino | copia os `.env` e os arquivos, sobe o Docker, instala as bibliotecas PHP, importa o banco e confere se as contagens batem |

> **O pacote contém as senhas dos `.env`.** Leve por pendrive ou nuvem pessoal, nunca pelo git (o repositório
> é público) nem por e-mail da empresa. Apague das duas máquinas depois de importar.

A base pública do CNPJ (`writable/cnpj`, ~5 GB) fica fora do pacote por padrão: baixe de novo na outra
máquina pela tela **Captação CNPJ**. Para levar mesmo assim: `bash scripts/migracao/exportar.sh --com-cnpj`.

## 1. Na máquina de origem

Com o Docker do painel rodando:

```bash
cd painel-clientes
bash scripts/migracao/exportar.sh
```

No Windows, rode no **Git Bash**. O script mostra a contagem de registros e o caminho do pacote.

Antes de levar o pacote, confira se o código está no GitHub (`git push`), porque a outra máquina baixa o
código de lá.

## 2. Preparar o Linux Mint (uma vez)

Instalar o Docker pelo repositório oficial (o Mint usa a base do Ubuntu):

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl git
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$UBUNTU_CODENAME") stable" \
  | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# usar o Docker sem sudo (saia da sessão e entre de novo depois deste comando)
sudo usermod -aG docker "$USER"
```

Conferir: `docker run --rm hello-world` e `docker compose version`.

Baixar o código:

```bash
cd ~
git clone https://github.com/tgplay/devspace.git
```

## 3. Na máquina de destino

```bash
cd ~/devspace/painel-clientes
bash scripts/migracao/importar.sh ~/Downloads/painel-migracao-AAAAMMDD-HHMM.tar.gz
```

A primeira vez demora alguns minutos (baixa as imagens do Docker e as bibliotecas PHP). No fim aparecem as
contagens: todas precisam dizer `ok`.

Depois abra http://localhost:8080/login (admin) e http://localhost:8081/login (cliente) e confira os dados.

### Segurança do importar.sh

- **Banco com dados:** se o banco da máquina de destino já tiver prospects, projetos, chamados ou usuários
  além do admin, o script **para sem mexer em nada**. Para substituir esses dados pelos do pacote:
  `bash scripts/migracao/importar.sh <pacote> --forcar`.
- **`.env` existente:** um `.env` que já existe no destino é mantido (o volume do banco pode ter sido
  criado com ele). Só os que faltam são copiados do pacote.
- **Contagens diferentes:** o script avisa e pede para não apagar o pacote.

## Usar nas duas máquinas

Não mantenha o painel ativo nas duas ao mesmo tempo: os bancos são independentes, e o que você cadastrar
numa não aparece na outra. Escolha uma máquina principal. Para trocar de máquina, exporte da que estava em
uso e importe na outra com `--forcar`.

A solução definitiva para usar de qualquer lugar é publicar o painel num servidor; o painel do cliente
também precisa estar na internet para os clientes aceitarem contratos e abrirem chamados.

## Testes feitos (29/10/2026)

- `exportar.sh` rodado no Windows (Git Bash) com o painel real: pacote de 12 KB com banco, uploads e os dois
  `.env`.
- Banco do pacote importado num PostgreSQL descartável criado só com o `db/init.sql` (como numa máquina
  nova): sem erro, e as 7 contagens bateram.
- Checagem de «banco com dados» do `importar.sh` rodada contra o banco real: detectou os registros e
  pediria `--forcar`.
- O `importar.sh` completo ainda não rodou no Linux: a primeira migração de verdade é o teste final.
