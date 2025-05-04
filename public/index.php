<?php
// Sæt fejlrapportering til maksimum under udvikling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Opsæt fejllogging
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

// Definer basestien
define('BASE_PATH', dirname(__DIR__));

// Indlæs router
require_once BASE_PATH . '/app/routes.php';

// Start routing
routeRequest();
