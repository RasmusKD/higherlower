<?php
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';

function routeRequest()
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $home = new HomeController();
    $auth = new AuthController();

    return match ([$uri, $method]) {
        ['/', 'GET']           => $home->index(),
        ['/login', 'POST']     => $auth->login(),
        ['/register', 'POST']  => $auth->register(),
        ['/logout', 'GET']     => $auth->logout(),
        default                => http_response_code(404) && exit("404 Not Found")
    };
}
