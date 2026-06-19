<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    // Rotas do painel acessíveis por agents (prefixos sem a barra inicial)
    private const AGENT_ALLOWED = [
        'admin',
        'admin/prospects',
        'admin/google-maps-import',
        'admin/wiki',
        'admin/profile',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Faça login para continuar.');
        }

        $role = session()->get('user_role');

        if ($role === 'admin') {
            return; // acesso total
        }

        if ($role === 'agent') {
            $uri = uri_string(); // ex: "admin/prospects/queue"
            foreach (self::AGENT_ALLOWED as $allowed) {
                if ($uri === $allowed || str_starts_with($uri, $allowed . '/')) {
                    return; // permitido
                }
            }
            return redirect()->to('/admin/prospects')->with('error', 'Acesso não autorizado.');
        }

        return redirect()->to('/app')->with('error', 'Acesso não autorizado.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
