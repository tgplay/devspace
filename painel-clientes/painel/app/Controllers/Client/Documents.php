<?php

namespace App\Controllers\Client;

use App\Models\ContractModel;
use App\Models\DocumentModel;
use CodeIgniter\Controller;

class Documents extends Controller
{
    public function index()
    {
        $clientId = session()->get('user_id');

        return view('client/documents', [
            'documents' => (new DocumentModel())
                ->where('client_id', $clientId)
                ->orderBy('created_at', 'DESC')
                ->findAll(),
            'contracts' => (new ContractModel())
                ->where('client_id', $clientId)
                ->whereIn('status', ['sent', 'accepted', 'closed'])
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ]);
    }
}
