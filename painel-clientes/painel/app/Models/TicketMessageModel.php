<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketMessageModel extends Model
{
    protected $table      = 'ticket_messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['ticket_id', 'sender_id', 'message'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
