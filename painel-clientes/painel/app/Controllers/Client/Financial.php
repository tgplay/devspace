<?php

namespace App\Controllers\Client;

use App\Models\InvoiceModel;
use CodeIgniter\Controller;

class Financial extends Controller
{
    public function index()
    {
        return view('client/financial', [
            'invoices' => (new InvoiceModel())->where('client_id', session()->get('user_id'))->orderBy('due_date', 'DESC')->findAll(),
        ]);
    }
}
