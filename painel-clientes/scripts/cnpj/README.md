# Importar leads da base aberta de CNPJ (Receita Federal)

Fonte gratuita de leads, sem as restrições de armazenamento do Google Maps. Contém empresas **ativas** com atividade (CNAE),
endereço, telefone e e-mail cadastrais. Não traz nota/avaliações nem diz se a empresa tem site (o comando usa o e-mail como pista).

## 1. Aplicar a migration (uma vez)

    Get-Content db\migrate_prospects_cnpj.sql -Raw | docker compose exec -T db psql -U painel_user -d painel_clientes

## 2. Baixar os arquivos

Baixe do portal oficial de dados abertos (conjunto "Cadastro Nacional da Pessoa Jurídica – CNPJ", em dados.gov.br — o endereço dos
arquivos já mudou no passado, siga o link da página oficial): **`Estabelecimentos0.zip` a `Estabelecimentos9.zip`** e **`Municipios.zip`**.
Os estabelecimentos são distribuídos entre os 10 arquivos sem critério geográfico, então é preciso baixar todos (vários GB).
Não descompacte: coloque os `.zip` em `painel\writable\cnpj\` (pasta ignorada pelo git). Layout oficial:
https://www.gov.br/receitafederal/dados/cnpj-metadados.pdf

A base é atualizada todo mês; baixe de novo quando quiser leads mais recentes.

## 3. Simular e importar

**Pela tela do painel (recomendado):** menu **Captação CNPJ** (`/admin/cnpj-import`). Preencha os filtros, clique em **Simular** (não grava nada)
e, se a amostra estiver boa, em **Importar**. O log aparece em tempo real e a tela avisa se os arquivos da Receita não forem encontrados.
Só uma importação roda por vez.

**Pelo terminal (equivalente):**

Primeiro com `--dry-run` (não grava nada):

    docker compose exec php php spark prospects:import-cnpj --municipio "SAO PAULO" --cnae 5611,1091 --bairro "PINHEIROS,VILA MADALENA,MOEMA" --competencia 2026-10 --dry-run

Se o resultado agradar, rode sem `--dry-run`. Opções:

| Opção | O que faz |
|---|---|
| `--municipio` | Nome do município sem acento (vírgula para vários). Obrigatório. |
| `--cnae` | Prefixos de CNAE (vírgula para vários). Obrigatório. |
| `--bairro` | Restringe a bairros. |
| `--limit` | Máximo de leads (padrão 500). |
| `--min-meses` | Idade mínima da empresa (padrão 6). |
| `--so-celular` | Só quem tem celular (provável WhatsApp). |
| `--incluir-dominio-proprio` | Mantém quem tem e-mail de domínio próprio (por padrão é descartado: provável site). |
| `--competencia` | Mês dos dados, só para registro. |
| `--dir` | Outra pasta dos `.zip`. |

CNAEs de alimentação usados com frequência (confira na tabela oficial do IBGE/CONCLA): `5611201` restaurantes, `5611203` lanchonetes,
`5611204`/`5611205` bares, `1091102` padarias (produção própria), `4721102` padarias (revenda), `5620102` buffets.

## O que o comando faz

- Só empresas com situação cadastral **ativa**, do município, CNAE e bairros informados, abertas há mais de `--min-meses`.
- Exige **nome fantasia** e telefone válido (prefere celular). Descarta duplicados (mesmo CNPJ ou telefone, inclusive de quem pediu opt-out).
- **E-mail:** domínio gratuito (gmail, hotmail...) é guardado e sugere que o negócio não tem site; domínio próprio descarta o lead (provável site); e-mail de escritório de contabilidade não é guardado (não é do negócio).
- Grava origem `cnpj`, o CNPJ, a base legal padrão (legítimo interesse), a origem do contato e o prazo de exclusão. Endereço e CNAE ficam nas anotações.

## Cuidados

- O telefone e o e-mail cadastrais podem ser da contabilidade ou estar desatualizados. Muitos MEIs não usam WhatsApp comercial.
- Empresas pequenas podem ser pessoa física na prática (MEI): mantenha a abordagem curta, identificada e com opção de SAIR (já incluída nas mensagens), e respeite o opt-out.
- O LGPD continua valendo para dados públicos. Valide a base legal (legítimo interesse) com um advogado.
