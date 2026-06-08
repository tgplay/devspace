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
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente não encontrado.']);
        }

        $newActive = $this->request->getPost('active') === '1';

        (new UserModel())->where('id', $id)->set(['active' => $newActive])->update();

        $status = $newActive ? 'ativado' : 'desativado';
        return $this->response->setJSON([
            'success' => true,
            'message' => "Cliente {$status} com sucesso.",
            'active'  => $newActive,
        ]);
    }

    public function bulkToggle()
    {
        $ids    = $this->request->getPost('ids');
        $action = $this->request->getPost('action');

        if (empty($ids) || ! in_array($action, ['activate', 'deactivate'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selecione ao menos um cliente e uma ação.']);
        }

        $active = $action === 'activate';
        (new UserModel())->whereIn('id', $ids)->where('role', 'client')->set(['active' => $active])->update();

        $label = $active ? 'ativados' : 'desativados';
        $count = count($ids);
        return $this->response->setJSON([
            'success' => true,
            'message' => "{$count} cliente(s) {$label} com sucesso.",
            'active'  => $active,
            'ids'     => array_map('intval', $ids),
        ]);
    }

    public function resetPassword(int $id)
    {
        $user = (new UserModel())->find($id);
        if (! $user || $user['role'] !== 'client') {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente não encontrado.']);
        }

        $password = $this->request->getPost('password');
        if (! $password || strlen($password) < 6) {
            return $this->response->setJSON(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres.']);
        }

        (new UserModel())->where('id', $id)->set(['password' => password_hash($password, PASSWORD_DEFAULT)])->update();

        return $this->response->setJSON(['success' => true, 'message' => 'Senha redefinida com sucesso.']);
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
