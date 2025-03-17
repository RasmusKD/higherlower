<?php

function routeRequest() {
    $uri = trim($_SERVER['REQUEST_URI'], '/');

    switch ($uri) {
        case '':
        case 'home':
            require_once __DIR__ . '/controllers/HomeController.php';
            home();
            break;
        case 'about':
            require_once __DIR__ . '/controllers/AboutController.php';
            about();
            break;
        default:
            http_response_code(404);
            echo "404 - Page Not Found";
            break;
    }
}
