<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Corrige o baseURL dinamicamente com base na porta real da requisição.
 *
 * Necessário porque o painel usa duas portas (8080 admin, 8081 cliente) na
 * mesma aplicação CI4. Sem isso, redirect()->back() e current_url() sempre
 * apontam para localhost:8080 (valor hardcoded em App.php), causando
 * redirecionamentos errados quando a requisição chega pela porta 8081.
 */
class DynamicBaseUrl implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
        $port = (int) ($request->getServer('SERVER_PORT') ?? 8080);
        config('App')->baseURL = 'http://localhost:' . $port . '/';
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void {}
}
