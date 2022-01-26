<?php

/**
 * Configuration file
 */

// Set application/json type 
header('Content-Type: application/json; charset=UTF-8');

// DB credentials
define('DB_USER', getenv('DB_USER'));
define('DB_PASS',  getenv('DB_PASS'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME',  getenv('DB_NAME'));

// SHA256 codec secret string
define('SECRET_KEY', getenv('SECRET_KEY'));

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
define('SHOW_ERRORS', true);

// CORS headers settings 
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');  // cache for 1 day
}
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}
