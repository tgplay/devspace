<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Profile extends Controller
{
    public function index(): string
    {
        $user = (new UserModel())->find(session()->get('user_id'));
        return view('admin/profile/index', ['title' => 'Meu Perfil', 'user' => $user]);
    }

    public function update()
    {
        $id    = session()->get('user_id');
        $model = new UserModel();
        $user  = $model->find($id);

        $name  = trim($this->request->getPost('name'));
        $email = trim($this->request->getPost('email'));

        if (strlen($name) < 2) {
            return redirect()->back()->with('error', 'Nome deve ter ao menos 2 caracteres.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'E-mail inválido.');
        }

        // Verifica duplicidade de e-mail (ignora o próprio usuário)
        $exists = $model->where('email', $email)->where('id !=', $id)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Este e-mail já está em uso por outro usuário.');
        }

        $data = ['name' => $name, 'email' => $email];

        $newPassword = $this->request->getPost('password');
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                return redirect()->back()->with('error', 'A senha deve ter ao menos 6 caracteres.');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $model->update($id, $data);

        // Atualiza o nome na sessão para refletir imediatamente no header e nas mensagens
        session()->set('user_name', $name);

        return redirect()->to('/admin/profile')->with('success', 'Perfil atualizado com sucesso.');
    }
}
