<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use TasksApp\Database;
use TasksApp\TaskGateway;
use TasksApp\TaskController;

require dirname(__DIR__) . '/vendor/autoload.php';

set_error_handler('TasksApp\ErrorHandler::handleError');
set_exception_handler('TasksApp\ErrorHandler::handleException');

$dotenv = Dotenv::createImmutable(dirname(__DIR__))->load();

$parts = explode(
    '/',
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$resource = $parts[2];

$id = empty($parts[3]) ? null : $parts[3];

if ($resource !== 'tasks') {
    http_response_code(404);
    exit;
}

header('Content-type: application/json; charset=UTF-8');

$database = new Database(
    user: $_ENV['DB_USER'],
    password: $_ENV['DB_PASS'],
    host: $_ENV['DB_HOST'],
    name: $_ENV['DB_NAME']
);

$taskGateway = new TaskGateway($database);

$controller = new TaskController($taskGateway);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
