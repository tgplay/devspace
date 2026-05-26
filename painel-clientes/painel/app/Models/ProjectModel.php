<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table      = 'projects';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['client_id', 'name', 'description', 'type', 'status', 'progress', 'deadline', 'delivery_url'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findAllWithClient(): array
    {
        return $this->db->table('projects p')
            ->select('p.*, u.name as client_name')
            ->join('users u', 'u.id = p.client_id')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
