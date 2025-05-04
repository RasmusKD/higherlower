<?php
if (preg_match('/\.(?:js|css|png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false; // Servér den anmodede ressource som den er.
}
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/GameController.php';

/**
 * Router funktion der dirigerer HTTP-anmodninger til de rette controllere
 * Matcher URI og HTTP-metode til de korrekte controllere og metoder
 */
function routeRequest()
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $home = new HomeController();
    $auth = new AuthController();
    $admin = new AdminController();
    $game = new GameController();

    return match ([$uri, $method]) {
        ['/', 'GET']               => $home->index(),
        ['/login', 'POST']         => $auth->login(),
        ['/register', 'POST']      => $auth->register(),
        ['/logout', 'GET']         => $auth->logout(),
        ['/admin', 'GET']          => $admin->index(),
        ['/admin/toggle-admin', 'POST'] => $admin->toggleAdminStatus(),
        ['/admin/update-api-key', 'POST'] => $admin->updateApiKey(),
        ['/game', 'GET']            => $game->index(),
        ['/game/start', 'GET']      => $game->start(),
        ['/game/guess', 'POST']     => $game->guess(),
        ['/leaderboard', 'GET']     => $game->leaderboard(),
        default                    => http_response_code(404) && exit("404 Ikke Fundet")
    };
}
