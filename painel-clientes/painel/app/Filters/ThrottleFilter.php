<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Limita requisições por IP em rotas públicas (ex.: /api/contact/ticket).
 * Padrão: 60 requisições por hora por IP (todas as do site chegam do mesmo IP, via relay). O formulário do site chega via
 * relay do WordPress, então o WP também aplica seu próprio limite por visitante.
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $ip  = $request->getIPAddress();
        $key = 'throttle_' . md5($ip . '|' . $request->getPath());

        if (! Services::throttler()->check($key, 60, HOUR)) {
            return Services::response()
                ->setStatusCode(429)
                ->setJSON(['success' => false, 'message' => 'Muitas tentativas. Tente novamente mais tarde.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
