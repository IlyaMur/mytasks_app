<?php

define('DB_USER', getenv('DB_USER'));
define('DB_PASS',  getenv('DB_PASS'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME',  getenv('DB_NAME'));

// var_dump(DB_PASS);
// var_dump(DB_USER);
// exit;

define('SECRET_KEY', getenv('SECRET_KEY'));

define('REFRESH_TOKEN_LIFESPAN', 15); // days
define('ACCESS_TOKEN_LIFESPAN', 1000000000); // seconds

// selecting type of auth 
// if JWT_AUTH is false - using basic X-Api-Key header key instead
define('JWT_AUTH', true);

set_error_handler('TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('TasksApp\Exceptions\ErrorHandler::handleException');

// cors settings 
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}
