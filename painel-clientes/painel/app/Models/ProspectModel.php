<?php

namespace App\Models;

use CodeIgniter\Model;

class ProspectModel extends Model
{
    protected $table      = 'prospects';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name', 'email', 'phone', 'company',
        'interest', 'source', 'status', 'notes',
        'rating', 'reviews_count', 'maps_url',
        'phone_followup_at', 'whatsapp_followup_at',
        'opt_out_at', 'legal_basis', 'contact_source', 'first_contact_at', 'delete_after',
        'google_place_id', 'places_released_at', 'cnpj',
    ];

    /** Lead cujo conteúdo do Google ainda não foi "liberado": nome/telefone/nota só existem ao vivo na API. */
    public static function isLivePlace(array $p): bool
    {
        return ! empty($p['google_place_id']) && empty($p['places_released_at']);
    }

    public const SOURCE_GOOGLE_MAPS = 'Google Maps (dados públicos do estabelecimento)';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function countByStatus(): array
    {
        $rows = $this->db->table('prospects')
            ->select('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }
}
