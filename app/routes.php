<?php
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';

function routeRequest() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($uri === '/' && $method === 'GET') {
        (new HomeController)->index();
    } elseif ($uri === '/login' && $method === 'POST') {
        (new AuthController)->login();
    } elseif ($uri === '/register' && $method === 'POST') {
        (new AuthController)->register();
    } elseif ($uri === '/logout' && $method === 'GET') {
        (new AuthController)->logout();
    } else {
        http_response_code(404);
        echo "404 Not Found";
    }
}
