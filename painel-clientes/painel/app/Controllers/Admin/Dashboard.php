<?php

namespace App\Controllers\Admin;

use App\Models\ProjectModel;
use App\Models\ProspectModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $data = [
            'total_clients'   => (new UserModel())->where('role', 'client')->countAllResults(),
            'total_projects'  => (new ProjectModel())->countAllResults(),
            'open_tickets'    => (new TicketModel())->whereIn('status', ['open', 'attending'])->countAllResults(),
            'new_prospects'   => (new ProspectModel())->where('status', 'new')->countAllResults(),
            'recent_clients'  => (new UserModel())->where('role', 'client')->orderBy('created_at', 'DESC')->findAll(5),
        ];

        return view('admin/dashboard', $data);
    }
}
