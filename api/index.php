<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

header('Content-type: application/json; charset=UTF-8');

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

$db = new TasksApp\Database(
    user: $_ENV['DB_USER'],
    password: $_ENV['DB_PASS'],
    host: $_ENV['DB_HOST'],
    name: $_ENV['DB_NAME']
);

$userGateway = new TasksApp\UserGateway($db);
$auth = new TasksApp\Auth($userGateway);

if (!$auth->authenticateAPIKey()) {
    exit;
}

$userId = $auth->getUserID();

$taskGateway = new TasksApp\TaskGateway($db);
$controller = new TasksApp\TaskController($taskGateway, $userId);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
