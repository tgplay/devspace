<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name', 'email', 'password', 'role', 'phone', 'avatar_url', 'active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email',
        'role'  => 'required|in_list[admin,client,agent]',
    ];
}
