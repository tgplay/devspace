<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Wiki extends Controller
{
    public function index(): string
    {
        return view('admin/wiki/index', ['title' => 'Manual do Painel']);
    }
}
