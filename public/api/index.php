<?php

declare(strict_types=1);

use TasksApp\Core\Auth;
use TasksApp\Core\Database;
use TasksApp\Gateways\TaskGateway;
use TasksApp\Gateways\UserGateway;
use TasksApp\Controllers\TaskController;
use TasksApp\Controllers\TokenController;

require dirname(__DIR__) . '/../vendor/autoload.php';
header('Content-type: application/json; charset=UTF-8');

$parts = explode(
    '/',
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$resource = $parts[2];
if (
    $resource !== 'tasks' &&
    $resource !== 'login'
) {
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

// generating auth token
if ($resource === 'login') {
    $tokenController = new TokenController(
        bodyData: (array) json_decode(file_get_contents("php://input"), true),
        userGateway: $userGateway,
        method: $_SERVER['REQUEST_METHOD']
    );

    $tokenController->processInputData();
    exit;
}

$auth = new Auth($userGateway);

if (!$auth->authenticateAPIKey()) {
    exit;
}

$userId = $auth->getUserID();

$taskGateway = new TaskGateway($db);
$taskController = new TaskController($taskGateway, $userId);

$id = empty($parts[3]) ? null : $parts[3];
$taskController->processRequest($_SERVER['REQUEST_METHOD'], $id);
