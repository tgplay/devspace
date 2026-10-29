<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Retenção de dados de prospecção (LGPD).
 *
 *   php spark prospects:purge            # executa
 *   php spark prospects:purge --dry-run  # só mostra o que faria
 *
 * - Prospectos vencidos (delete_after < hoje) que NÃO são clientes e NÃO pediram opt-out: excluídos.
 * - Prospectos vencidos com opt-out: anonimizados, mantendo só o telefone na lista de supressão
 *   (senão uma nova importação do Google Maps os traria de volta).
 */
class PurgeProspects extends BaseCommand
{
    protected $group       = 'Prospects';
    protected $name        = 'prospects:purge';
    protected $description = 'Exclui/anonimiza prospectos cujo prazo de retenção (delete_after) venceu.';
    protected $usage       = 'prospects:purge [--dry-run]';

    public function run(array $params)
    {
        $dry = CLI::getOption('dry-run') !== null || in_array('--dry-run', $params, true);
        $db  = \Config\Database::connect();

        $delete = $db->table('prospects')
            ->where('delete_after <', date('Y-m-d'))
            ->where('status !=', 'won')
            ->where('opt_out_at', null);

        $scrub = $db->table('prospects')
            ->where('delete_after <', date('Y-m-d'))
            ->where('opt_out_at IS NOT NULL', null, false)
            ->where('name !=', '[removido]');

        $nDelete = (clone $delete)->countAllResults();
        $nScrub  = (clone $scrub)->countAllResults();

        CLI::write("A excluir: {$nDelete} | A anonimizar (opt-out): {$nScrub}" . ($dry ? ' [dry-run]' : ''));
        if ($dry) {
            return;
        }

        $delete->delete();
        $scrub->update([
            'name'     => '[removido]',
            'email'    => null,
            'company'  => null,
            'notes'    => null,
            'maps_url' => null,
            'rating'   => null,
            'reviews_count' => null,
        ]);

        CLI::write('Concluído.', 'green');
    }
}
