<?php
defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('gps/v1', '/contact', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'gps_contact_submit',
        'permission_callback' => '__return_true',
    ]);
});

function gps_contact_submit(WP_REST_Request $request): WP_REST_Response
{
    $name    = sanitize_text_field($request->get_param('name')    ?? '');
    $email   = sanitize_email($request->get_param('email')        ?? '');
    $phone   = sanitize_text_field($request->get_param('phone')   ?? '');
    $message = sanitize_textarea_field($request->get_param('message') ?? '');
    $tipo    = sanitize_key($request->get_param('tipo')           ?? 'geral');

    if ($name === '' || $email === '' || $message === '') {
        return new WP_REST_Response(
            ['success' => false, 'message' => 'Preencha todos os campos obrigatórios.'],
            422
        );
    }

    // Relay para o CI4 via rede interna Docker (nginx:8080)
    $relay = wp_remote_post('http://nginx:8080/api/contact/ticket', [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'body'    => http_build_query(compact('name', 'email', 'phone', 'message', 'tipo')),
        'timeout' => 10,
    ]);

    if (is_wp_error($relay)) {
        return new WP_REST_Response(
            ['success' => false, 'message' => 'Serviço temporariamente indisponível. Tente novamente.'],
            503
        );
    }

    $body = json_decode(wp_remote_retrieve_body($relay), true);
    $code = (int) wp_remote_retrieve_response_code($relay);

    return new WP_REST_Response(
        $body ?? ['success' => false, 'message' => 'Erro inesperado.'],
        $code ?: 200
    );
}
