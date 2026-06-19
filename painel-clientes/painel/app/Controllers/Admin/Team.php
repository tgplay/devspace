<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Team extends Controller
{
    public function index(): string
    {
        $members = (new UserModel())
            ->whereIn('role', ['admin', 'agent'])
            ->orderBy('role', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/team/index', ['title' => 'Equipe', 'members' => $members]);
    }

    public function create(): string
    {
        return view('admin/team/form', ['title' => 'Novo Membro', 'member' => null]);
    }

    public function store()
    {
        $name     = trim($this->request->getPost('name'));
        $email    = trim($this->request->getPost('email'));
        $role     = $this->request->getPost('role');
        $password = $this->request->getPost('password');

        if (strlen($name) < 2) {
            return redirect()->back()->withInput()->with('error', 'Nome deve ter ao menos 2 caracteres.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'E-mail inválido.');
        }
        if (! in_array($role, ['admin', 'agent'], true)) {
            return redirect()->back()->withInput()->with('error', 'Nível de acesso inválido.');
        }
        if (strlen($password) < 6) {
            return redirect()->back()->withInput()->with('error', 'Senha deve ter ao menos 6 caracteres.');
        }

        $model = new UserModel();
        if ($model->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'E-mail já cadastrado.');
        }

        $model->insert([
            'name'     => $name,
            'email'    => $email,
            'role'     => $role,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'active'   => true,
        ]);

        return redirect()->to('/admin/team')->with('success', "Membro \"{$name}\" criado com sucesso.");
    }

    public function show(int $id): string
    {
        $member = (new UserModel())->whereIn('role', ['admin', 'agent'])->find($id);
        if (! $member) {
            return redirect()->to('/admin/team')->with('error', 'Membro não encontrado.');
        }
        return view('admin/team/form', ['title' => 'Editar Membro', 'member' => $member]);
    }

    public function update(int $id)
    {
        $model  = new UserModel();
        $member = $model->whereIn('role', ['admin', 'agent'])->find($id);
        if (! $member) {
            return redirect()->to('/admin/team')->with('error', 'Membro não encontrado.');
        }

        // Protege o próprio usuário de rebaixar sua role acidentalmente
        if ($id === (int) session()->get('user_id') && $this->request->getPost('role') !== 'admin') {
            return redirect()->back()->with('error', 'Você não pode alterar sua própria role.');
        }

        $name  = trim($this->request->getPost('name'));
        $email = trim($this->request->getPost('email'));
        $role  = $this->request->getPost('role');

        if (strlen($name) < 2) {
            return redirect()->back()->with('error', 'Nome deve ter ao menos 2 caracteres.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'E-mail inválido.');
        }
        if (! in_array($role, ['admin', 'agent'], true)) {
            return redirect()->back()->with('error', 'Nível de acesso inválido.');
        }

        $exists = $model->where('email', $email)->where('id !=', $id)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'E-mail já está em uso por outro usuário.');
        }

        $data = ['name' => $name, 'email' => $email, 'role' => $role];

        $newPassword = $this->request->getPost('password');
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                return redirect()->back()->with('error', 'Senha deve ter ao menos 6 caracteres.');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $model->update($id, $data);

        // Atualiza sessão se o admin editou o próprio nome
        if ($id === (int) session()->get('user_id')) {
            session()->set('user_name', $name);
        }

        return redirect()->to('/admin/team')->with('success', "Membro \"{$name}\" atualizado.");
    }

    public function delete(int $id)
    {
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/admin/team')->with('error', 'Você não pode excluir sua própria conta.');
        }

        $model  = new UserModel();
        $member = $model->whereIn('role', ['admin', 'agent'])->find($id);
        if (! $member) {
            return redirect()->to('/admin/team')->with('error', 'Membro não encontrado.');
        }

        $model->delete($id);

        return redirect()->to('/admin/team')->with('success', "Membro \"{$member['name']}\" removido.");
    }
}
