<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

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

$database = new TasksApp\Database(
    user: $_ENV['DB_USER'],
    password: $_ENV['DB_PASS'],
    host: $_ENV['DB_HOST'],
    name: $_ENV['DB_NAME']
);

$userGateway = new TasksApp\UserGateway($database);
$auth = new TasksApp\Auth($userGateway);

if (!$auth->authenticateAPIKey()) {
    exit;
}

$taskGateway = new TasksApp\TaskGateway($database);
$controller = new TasksApp\TaskController($taskGateway);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
