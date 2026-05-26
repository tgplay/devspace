<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['client_id', 'subject', 'status', 'priority'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findAllWithClient(): array
    {
        return $this->db->table('tickets t')
            ->select('t.*, u.name as client_name')
            ->join('users u', 'u.id = t.client_id')
            ->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
