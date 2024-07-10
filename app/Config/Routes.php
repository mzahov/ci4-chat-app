<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// Chat Routes
$routes->get('/', 'Api\ChatController::index', ['filter'=>'session']);

$routes->group('chat', ['filter'=>'jwt', 'namespace'=>'\App\Controllers\Api'], static function($routes) {
    $routes->get('get-messages', 'ChatController::getMessages');
    $routes->get('list', 'ChatController::getUserRooms');

    $routes->post('upload', 'FileController::uploadRoomFile');
});
$routes->get('chat/show-file/(:num)/(:any)/(:alpha)', 'Api\FileController::showFile/$1/$2/$3');

$routes->presenter('chat', ['except' => 'index', 'filter'=> 'jwt', 'namespace' => '\App\Controllers\Api', 'controller' => 'ChatController']);
// End Chat Routes

// Auth Routes
$routes->group('auth', ['namespace'=>'\App\Controllers\Api\Shield'], static function($routes) {
    $routes->post('jwt', 'LoginController::jwtLogin');
    $routes->get('token', 'LoginController::issueJwt', ['filter' => 'session']);
    $routes->post('refresh', 'LoginController::refreshJwt');
});

service('auth')->routes($routes);
// End Auth Routes