<?php

namespace App\Models;

use CodeIgniter\Model;

class ProspectCallModel extends Model
{
    protected $table      = 'prospect_calls';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['prospect_id', 'notes'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
