<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('api/servicios', 'Servicios::index');
$routes->get('tipos-servicio', 'Servicios::gestionarTipos');
$routes->post('tipos-servicio', 'Servicios::crearTipo');
$routes->post('tipos-servicio/(:num)', 'Servicios::actualizarTipo/$1');
$routes->post('tipos-servicio/(:num)/baja', 'Servicios::darDeBajaTipo/$1');
$routes->get('servicios', 'Servicios::gestionar');
$routes->post('servicios/asociar', 'Servicios::asociar');
$routes->post('servicios/asociaciones/(:num)', 'Servicios::actualizarAsociacion/$1');
$routes->post('servicios/asociaciones/(:num)/baja', 'Servicios::darDeBajaAsociacion/$1');
$routes->get('inmuebles', 'Inmuebles::index');
$routes->post('inmuebles', 'Inmuebles::create');
$routes->post('inmuebles/(:num)', 'Inmuebles::update/$1');
$routes->post('inmuebles/(:num)/baja', 'Inmuebles::deactivate/$1');
