<?php

// set dir for logging
ini_set(
    'error_log',
    dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt'
);

// showing errors, if false - logging
define('SHOW_ERRORS', true);

// db credentials
define('DB_USER', getenv('DB_USER'));
define('DB_PASS',  getenv('DB_PASS'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME',  getenv('DB_NAME'));

// sha256 codec secret string for JWT encoding
define('SECRET_KEY', getenv('SECRET_KEY'));

// setting for adjusting lifespan of the JWT tokens 
define('REFRESH_TOKEN_LIFESPAN', 15); // days
define('ACCESS_TOKEN_LIFESPAN', 1000000000); // seconds

// selecting type of auth 
// if JWT_AUTH is false - using basic X-Api-Key header key instead
define('JWT_AUTH', false);

// hight level error handlers
set_error_handler('TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('TasksApp\Exceptions\ErrorHandler::handleException');

// CORS headers settings 
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');  // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}
