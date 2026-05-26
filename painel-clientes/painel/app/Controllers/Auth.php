<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    public function loginForm()
    {
        if (session()->get('user_id')) {
            return $this->redirectByRole();
        }
        return view('auth/login');
    }

    public function login()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = (new UserModel())->where('email', $email)->where('active', true)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'E-mail ou senha inválidos.');
        }

        $section = $this->request->getServer('APP_SECTION');

        if ($section === 'admin' && $user['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Suas credenciais não têm acesso a este portal.');
        }

        if ($section === 'client' && $user['role'] !== 'client') {
            return redirect()->back()->with('error', 'Suas credenciais não têm acesso a este portal.');
        }

        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
        ]);

        return $this->redirectByRole();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function registerForm()
    {
        return view('auth/register');
    }

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[120]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new UserModel())->insert([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'     => 'client',
        ]);

        return redirect()->to('/login')->with('success', 'Conta criada! Faça seu login.');
    }

    private function redirectByRole(): \CodeIgniter\HTTP\RedirectResponse
    {
        return session()->get('user_role') === 'admin'
            ? redirect()->to('http://localhost:8080/admin')
            : redirect()->to('http://localhost:8081/app');
    }
}
