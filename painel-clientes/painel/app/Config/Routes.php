<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── API pública (site WordPress → CI4) ─────────────────────────────────────
$routes->post('api/contact/ticket', 'Api\Contact::ticket');

// ── Autenticação (pública) ──────────────────────────────────────────────────
$routes->get('/',              'Auth::loginForm');
$routes->get('login',          'Auth::loginForm');
$routes->post('login',         'Auth::login');
$routes->get('logout',         'Auth::logout');
$routes->get('register',       'Auth::registerForm');
$routes->post('register',      'Auth::register');
$routes->get('admin/stop-impersonating', 'Admin\Clients::stopImpersonating');

// ── Painel Admin ────────────────────────────────────────────────────────────
// Acessível em localhost:8080  (Nginx envia APP_SECTION=admin)
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('/',                    'Admin\Dashboard::index');
    $routes->get('clients',                    'Admin\Clients::index');
    $routes->post('clients/(:num)/toggle',     'Admin\Clients::toggle/$1');
    $routes->post('clients/bulk-toggle',       'Admin\Clients::bulkToggle');
    $routes->get('clients/(:num)',             'Admin\Clients::show/$1');
    $routes->post('clients/(:num)/update',         'Admin\Clients::update/$1');
    $routes->post('clients/(:num)/reset-password', 'Admin\Clients::resetPassword/$1');
    $routes->get('clients/(:num)/login',       'Admin\Clients::loginAs/$1');
    $routes->get('projects',             'Admin\Projects::index');
    $routes->get('projects/new',         'Admin\Projects::create');
    $routes->post('projects',            'Admin\Projects::store');
    $routes->post('projects/bulk-rename',  'Admin\Projects::bulkRename');
    $routes->get('projects/(:num)',        'Admin\Projects::show/$1');
    $routes->post('projects/(:num)',       'Admin\Projects::update/$1');
    $routes->post('projects/(:num)/rename','Admin\Projects::rename/$1');
    $routes->get('support',              'Admin\Support::index');
    $routes->get('support/(:num)',       'Admin\Support::show/$1');
    $routes->post('support/(:num)',      'Admin\Support::reply/$1');
});

// ── Dashboard do Cliente ────────────────────────────────────────────────────
// Acessível em localhost:8081  (Nginx envia APP_SECTION=client)
$routes->group('app', ['filter' => 'auth'], function ($routes) {
    $routes->get('/',                          'Client\Dashboard::index');
    $routes->get('projects',                   'Client\Projects::index');
    $routes->get('projects/(:num)',            'Client\Projects::show/$1');
    $routes->post('tasks/(:num)/approve',      'Client\Tasks::approve/$1');
    $routes->post('tasks/(:num)/request-revision', 'Client\Tasks::requestRevision/$1');
    $routes->get('support',                    'Client\Support::index');
    $routes->get('support/new',                'Client\Support::newTicket');
    $routes->post('support',                   'Client\Support::store');
    $routes->get('support/(:num)',             'Client\Support::show/$1');
    $routes->post('support/(:num)/reply',      'Client\Support::reply/$1');
    $routes->get('financial',                  'Client\Financial::index');
    $routes->get('documents',                  'Client\Documents::index');
});
