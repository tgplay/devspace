<?php

namespace App\Controllers\Client;

use App\Models\InvoiceModel;
use App\Models\NotificationModel;
use App\Models\ProjectModel;
use App\Models\TicketModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $clientId = session()->get('user_id');

        return view('client/dashboard', [
            'projects'      => (new ProjectModel())->where('client_id', $clientId)->findAll(),
            'open_tickets'  => (new TicketModel())->where('client_id', $clientId)->whereIn('status', ['open', 'attending'])->countAllResults(),
            'pending_invoices' => (new InvoiceModel())->where('client_id', $clientId)->whereIn('status', ['pending', 'overdue'])->findAll(),
            'notifications' => (new NotificationModel())->where('user_id', $clientId)->where('read_at', null)->orderBy('created_at', 'DESC')->findAll(10),
        ]);
    }
}
