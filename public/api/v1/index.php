<?php

declare(strict_types=1);

use TasksApp\Gateways\TaskGateway;
use TasksApp\Gateways\UserGateway;
use TasksApp\Controllers\TaskController;
use TasksApp\Core\Database;

require dirname(__DIR__) . '/../../vendor/autoload.php';

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

$db = new Database(
    user: DB_USER,
    password: DB_PASS,
    host: DB_HOST,
    name: DB_NAME
);

$userGateway = new UserGateway($db);
$auth = new TasksApp\Core\Auth($userGateway);

if (!$auth->authenticateAPIKey()) {
    exit;
}

$userId = $auth->getUserID();

$taskGateway = new TaskGateway($db);
$controller = new TaskController($taskGateway, $userId);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
