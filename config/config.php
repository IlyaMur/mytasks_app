<?php

/**
 * Configuration file
 */

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// DB credentials
define('DB_USER', $_ENV['MYSQL_USER']);
define('DB_PASS',  $_ENV['MYSQL_PASSWORD']);
define('DB_HOST', $_ENV['MYSQL_HOST']);
define('DB_NAME',  $_ENV['MYSQL_DATABASE']);

// Set application/json type 
header('Content-Type: application/json; charset=UTF-8');

// SHA256 codec secret string
define('SECRET_KEY', $_ENV['JWT_KEY']);

// Adjusting lifetime of the JWT tokens 
define('REFRESH_TOKEN_LIFESPAN', 5); // days
define('ACCESS_TOKEN_LIFESPAN', 300); // seconds

// Selection type of auth 
// If JWT_AUTH is false - using a basic X-Api-Key header key instead
define('JWT_AUTH', true);

// Hight level error handlers
set_error_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleException');

// Set a dir for logging
ini_set(
    'error_log',
    dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt'
);

// Showing errors, if false - logging
define('SHOW_ERRORS', false);

// CORS headers settings 
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Allow only the React App deployed to the GH Pages 
    header("Access-Control-Allow-Origin: https://ilyamur.github.io");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');  // cache for 1 day
}
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}
