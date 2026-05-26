<?php

namespace App\Controllers\Client;

use App\Models\DocumentModel;
use CodeIgniter\Controller;

class Documents extends Controller
{
    public function index()
    {
        return view('client/documents', [
            'documents' => (new DocumentModel())->where('client_id', session()->get('user_id'))->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }
}
