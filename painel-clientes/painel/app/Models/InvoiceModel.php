<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'client_id', 'project_id', 'description', 'amount',
        'status', 'due_date', 'paid_at', 'boleto_url', 'nf_url',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
