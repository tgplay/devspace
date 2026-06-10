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
    ];

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
