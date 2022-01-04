<?php

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);

set_error_handler('TasksApp\Core\ErrorHandler::handleError');
set_exception_handler('TasksApp\Core\ErrorHandler::handleException');
