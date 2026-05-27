<?php

namespace App\Controllers\Admin;

use App\Models\InvoiceModel;
use App\Models\ProjectModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Clients extends Controller
{
    public function index()
    {
        return view('admin/clients/index', [
            'clients' => (new UserModel())->where('role', 'client')->orderBy('name')->findAll(),
        ]);
    }

    public function show(int $id)
    {
        $client = (new UserModel())->find($id);
        if (! $client || $client['role'] !== 'client') {
            return redirect()->to('/admin/clients')->with('error', 'Cliente não encontrado.');
        }

        return view('admin/clients/show', [
            'client'   => $client,
            'projects' => (new ProjectModel())->where('client_id', $id)->findAll(),
            'tickets'  => (new TicketModel())->where('client_id', $id)->orderBy('created_at', 'DESC')->findAll(5),
            'invoices' => (new InvoiceModel())->where('client_id', $id)->orderBy('due_date', 'DESC')->findAll(5),
        ]);
    }

    public function toggle(int $id)
    {
        $user = (new UserModel())->find($id);
        if (! $user || $user['role'] !== 'client') {
            return redirect()->to('/admin/clients')->with('error', 'Cliente não encontrado.');
        }

        $isActive = filter_var($user['active'], FILTER_VALIDATE_BOOLEAN);
        (new UserModel())->update($id, ['active' => ! $isActive]);

        $status = $isActive ? 'desativado' : 'ativado';
        return redirect()->to('/admin/clients')->with('success', "Cliente {$status} com sucesso.");
    }

    public function bulkToggle()
    {
        $ids    = $this->request->getPost('ids');
        $action = $this->request->getPost('action');

        if (empty($ids) || ! in_array($action, ['activate', 'deactivate'])) {
            return redirect()->to('/admin/clients')->with('error', 'Selecione ao menos um cliente e uma ação.');
        }

        $active = $action === 'activate';
        (new UserModel())->whereIn('id', $ids)->where('role', 'client')->set(['active' => $active])->update();

        $label = $active ? 'ativados' : 'desativados';
        $count = count($ids);
        return redirect()->to('/admin/clients')->with('success', "{$count} cliente(s) {$label} com sucesso.");
    }

    public function loginAs(int $id)
    {
        $client = (new UserModel())->find($id);
        if (! $client || $client['role'] !== 'client') {
            return redirect()->to('/admin/clients')->with('error', 'Cliente não encontrado.');
        }

        session()->set('impersonating', session()->get('user_id'));
        session()->set([
            'user_id'   => $client['id'],
            'user_name' => $client['name'],
            'user_role' => 'client',
        ]);

        return redirect()->to('http://localhost:8081/app');
    }

    public function stopImpersonating()
    {
        $adminId = session()->get('impersonating');
        if (! $adminId) {
            return redirect()->to('/admin');
        }

        $admin = (new UserModel())->find($adminId);
        if (! $admin || $admin['role'] !== 'admin') {
            return redirect()->to('/login');
        }

        session()->remove('impersonating');
        session()->set([
            'user_id'   => $admin['id'],
            'user_name' => $admin['name'],
            'user_role' => 'admin',
        ]);

        return redirect()->to('http://localhost:8080/admin/clients');
    }
}
