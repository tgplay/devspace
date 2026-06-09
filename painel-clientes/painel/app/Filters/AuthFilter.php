<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Faça login para continuar.');
        }

        if (session()->get('user_role') === 'admin') {
            return redirect()->to('http://localhost:8080/admin');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
