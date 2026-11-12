<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Init::login');

$routes->get('/sing_up', 'Init::singUp');

$routes->get('/home', 'Home::home', ['filter' => 'auth']);

$routes->post('/login', 'AuthController::login');

$routes->post('/sing_up', 'AuthController::singUp');

$routes->post('/logout', 'AuthController::logout', ['filter' => 'auth']);

$routes->post('/admin/solicitudes/(:num)/aprobar', 'AdminController::approveUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
