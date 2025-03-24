<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/routes.php';

// Call the router
routeRequest();
