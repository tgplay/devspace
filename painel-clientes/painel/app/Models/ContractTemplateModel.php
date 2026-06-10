<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractTemplateModel extends Model
{
    protected $table         = 'contract_templates';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = ['name', 'content'];
}
