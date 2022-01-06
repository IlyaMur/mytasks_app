<?php

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('SECRET_KEY', $_ENV['SECRET_KEY']);

// selecting type of auth 
// if TOKEN_AUTH is false - using api key instead
define('JWT_AUTH', true);

set_error_handler('TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('TasksApp\Exceptions\ErrorHandler::handleException');
