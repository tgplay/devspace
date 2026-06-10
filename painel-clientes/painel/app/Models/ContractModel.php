<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table         = 'contracts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'client_id', 'project_id', 'template_id', 'title', 'content',
        'value', 'start_date', 'end_date', 'status', 'accepted_at', 'accepted_ip',
    ];

    public function withClient(): static
    {
        return $this
            ->select('contracts.*, users.name AS client_name, users.email AS client_email')
            ->join('users', 'users.id = contracts.client_id');
    }
}
