<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Init::landing');

$routes->get('/login', 'Init::login');

$routes->get('/sing_up', 'Init::singUp');

$routes->get('/home', 'Home::home', ['filter' => 'auth']);
$routes->get('/usuarios', 'AdminController::users', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->get('/usuarios/(:num)/editar', 'AdminController::editUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->post('/usuarios/(:num)', 'AdminController::updateUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->post('/usuarios/(:num)/baja', 'AdminController::deactivateUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->get('/usuarios/(:num)/inmuebles', 'AdminController::userProperties/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);

$routes->post('/login', 'AuthController::login');

$routes->post('/sing_up', 'AuthController::singUp');

$routes->post('/logout', 'AuthController::logout', ['filter' => 'auth']);

$routes->post('/admin/solicitudes/(:num)/aprobar', 'AdminController::approveUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->post('/admin/solicitudes/(:num)/rechazar', 'AdminController::rejectUser/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);
$routes->post('/admin/solicitudes/(:num)/reabrir', 'AdminController::reopenUserRequest/$1', [
    'filter' => ['auth', 'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR],
]);


// ==========================================
// INMUEBLES - PROPIETARIO
// ==========================================
$routes->group('inmuebles', [
    'filter' => [
        'auth',
        'role:' . \App\Models\RolModel::ROLE_OWNER
    ]
], static function ($routes) {
    $routes->get('/', 'Inmuebles::index');
    $routes->get('geo/provincias', 'Inmuebles::geoProvinces');
    $routes->get('geo/provincias/(:segment)/localidades', 'Inmuebles::geoLocalities/$1');
    $routes->post('/', 'Inmuebles::create');
    $routes->post('(:num)', 'Inmuebles::update/$1');
    $routes->post('(:num)/baja', 'Inmuebles::deactivate/$1');
});


// ==========================================
// SERVICIOS ASOCIADOS - PROPIETARIO
// ==========================================
$routes->group('servicios', [
    'filter' => [
        'auth',
        'role:' . \App\Models\RolModel::ROLE_OWNER
    ]
], static function ($routes) {
    // Endpoint JSON con los tipos de servicios
    $routes->get('/', 'Servicios::index');

    // Gestión de servicios asociados a inmuebles
    $routes->get('asociaciones', 'Servicios::gestionar');
    $routes->post('asociar', 'Servicios::asociar');
    $routes->post('asociaciones/(:num)', 'Servicios::actualizarAsociacion/$1');
    $routes->post('asociaciones/(:num)/baja', 'Servicios::darDeBajaAsociacion/$1');
});


// ==========================================
// FACTURAS - PROPIETARIO
// ==========================================
$routes->group('facturas', [
    'filter' => [
        'auth',
        'role:' . \App\Models\RolModel::ROLE_OWNER,
    ],
], static function ($routes) {
    $routes->get('/', 'Facturas::index');
    $routes->get('nueva', 'Facturas::new');
    $routes->get('informe', 'Facturas::report');
    $routes->get('(:num)', 'Facturas::show/$1');
    $routes->post('/', 'Facturas::create');
    $routes->post('(:num)/pago', 'Facturas::registerPayment/$1');
});


// ==========================================
// CATÁLOGO DE TIPOS DE SERVICIOS - ADMIN
// ==========================================
$routes->group('tipos-servicio', [
    'filter' => [
        'auth',
        'role:' . \App\Models\RolModel::ROLE_ADMINISTRATOR
    ]
], static function ($routes) {
    $routes->get('/', 'Servicios::gestionarTipos');
    $routes->post('/', 'Servicios::crearTipo');
    $routes->post('(:num)', 'Servicios::actualizarTipo/$1');
    $routes->post('(:num)/baja', 'Servicios::darDeBajaTipo/$1');
});