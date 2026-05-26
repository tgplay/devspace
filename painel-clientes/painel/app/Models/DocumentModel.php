<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table      = 'documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['client_id', 'project_id', 'name', 'type', 'file_path'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false;
}
