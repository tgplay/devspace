<?php

namespace App\Commands;

use App\Libraries\CnpjImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Importa leads da base aberta de CNPJ da Receita Federal (arquivos Estabelecimentos*.zip + Municipios.zip).
 *
 *   php spark prospects:import-cnpj --municipio "SAO PAULO" --cnae 5611,1091 --bairro "PINHEIROS,MOEMA" --dry-run
 *
 * A lógica fica em App\Libraries\CnpjImporter (compartilhada com a tela admin "Captação CNPJ").
 */
class ImportCnpj extends BaseCommand
{
    protected $group       = 'Prospects';
    protected $name        = 'prospects:import-cnpj';
    protected $description = 'Importa prospectos da base aberta de CNPJ (Receita Federal).';
    protected $usage       = 'prospects:import-cnpj --municipio "SAO PAULO" --cnae 5611,1091 [--bairro "PINHEIROS,MOEMA"] [--dir pasta] [--competencia 2026-10] [--limit 500] [--min-meses 6] [--so-celular] [--incluir-dominio-proprio] [--dry-run]';
    protected $options     = [
        '--municipio'              => 'Nome(s) do município, separados por vírgula (obrigatório).',
        '--cnae'                   => 'Prefixos de CNAE, separados por vírgula, ex.: 5611,1091 (obrigatório).',
        '--bairro'                 => 'Bairros (opcional), separados por vírgula.',
        '--dir'                    => 'Pasta com os .zip (padrão: writable/cnpj).',
        '--competencia'            => 'Mês de referência dos dados, só para registro (ex.: 2026-10).',
        '--limit'                  => 'Máximo de leads a importar (padrão 500).',
        '--min-meses'              => 'Idade mínima da empresa em meses (padrão 6).',
        '--so-celular'             => 'Só importa quem tem celular (provável WhatsApp).',
        '--incluir-dominio-proprio' => 'Não descarta quem tem e-mail de domínio próprio.',
        '--dry-run'                => 'Mostra o que seria importado, sem gravar.',
    ];

    public function run(array $params)
    {
        $municipios = CnpjImporter::csv(CLI::getOption('municipio'));
        $cnaes      = CnpjImporter::csv(CLI::getOption('cnae'));
        if (! $municipios || ! $cnaes) {
            CLI::error('Informe --municipio e --cnae. Exemplo: --municipio "SAO PAULO" --cnae 5611,1091');
            return;
        }

        $importer = new CnpjImporter([
            'municipio'               => $municipios,
            'cnae'                    => $cnaes,
            'bairro'                  => CnpjImporter::csv(CLI::getOption('bairro')),
            'dir'                     => (string) (CLI::getOption('dir') ?: WRITEPATH . 'cnpj'),
            'competencia'             => (string) (CLI::getOption('competencia') ?: 'não informada'),
            'limit'                   => (int) (CLI::getOption('limit') ?: 500),
            'min_meses'               => (int) (CLI::getOption('min-meses') ?? 6),
            'so_celular'              => CLI::getOption('so-celular') !== null,
            'incluir_dominio_proprio' => CLI::getOption('incluir-dominio-proprio') !== null,
            'dry_run'                 => CLI::getOption('dry-run') !== null,
        ], function (string $type, array $d) {
            static $last = 0;
            if ($type === 'info') {
                CLI::write($d['message']);
            } elseif ($type === 'progress' && (time() - $last) >= 15) {
                $last = time();
                CLI::write("  ... {$d['lines']} linhas lidas, {$d['imported']} importados");
            }
        });

        try {
            $r = $importer->run();
        } catch (\RuntimeException $e) {
            CLI::error($e->getMessage());
            return;
        }

        CLI::newLine();
        CLI::write(($r['dry_run'] ? 'Simulação: ' : '') . "{$r['imported']} lead(s) " . ($r['dry_run'] ? 'seriam importados' : 'importados') . " ({$r['lines']} linhas lidas).", 'green');
        foreach ($r['samples'] as $s) {
            CLI::write('  • ' . $s);
        }
        CLI::write('Ignorados: ' . implode(', ', array_map(fn($k, $v) => "{$k}={$v}", array_keys($r['skipped']), $r['skipped'])));
    }
}
