<?php

declare(strict_types=1);

use TasksApp\Core\Auth;
use TasksApp\Core\Database;
use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\TaskGateway;
use TasksApp\Gateways\UserGateway;
use TasksApp\Gateways\RefreshTokenGateway;
use TasksApp\Controllers\TaskController;
use TasksApp\Controllers\TokenController;
use TasksApp\Controllers\RefreshTokenController;

require dirname(__DIR__) . '/../vendor/autoload.php';

header('Content-type: application/json; charset=UTF-8');

$parts = explode(
    '/',
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$db = new Database(
    user: DB_USER,
    password: DB_PASS,
    host: DB_HOST,
    name: DB_NAME
);
$userGateway = new UserGateway($db);
$refreshTokenGateway = new RefreshTokenGateway($db, SECRET_KEY);

$resource = $parts[2];
switch ($resource) {
    case 'login':
        // generating auth token
        $tokenController = new TokenController(
            bodyData: (array) json_decode(file_get_contents("php://input"), true),
            userGateway: $userGateway,
            refreshTokenGateway: $refreshTokenGateway,
            method: $_SERVER['REQUEST_METHOD']
        );
        $tokenController->processInputData();
        exit;
    case 'refresh':
        $refreshTokenController = new RefreshTokenController(
            bodyData: (array) json_decode(file_get_contents("php://input"), true),
            refreshTokenGateway: $refreshTokenGateway,
            userGateway: $userGateway,
            method: $_SERVER['REQUEST_METHOD']
        );
        $refreshTokenController->processInputData();
        exit;
    case 'tasks':
        $auth = new Auth($userGateway, new JWTCodec(SECRET_KEY));
        // selecting type of auth (token or api key)
        if (JWT_AUTH) {
            $isAuthCorrect = $auth->authenticateAccessToken();
        } else {
            $isAuthCorrect = $auth->authenticateAPIKey();
        }

        if (!$isAuthCorrect) {
            exit;
        }

        break;
    default:
        http_response_code(404);
        exit;
}


$userId = $auth->getUserID();

$taskGateway = new TaskGateway($db);
$taskController = new TaskController($taskGateway, $userId);

$taskId = empty($parts[3]) ? null : $parts[3];
$taskController->processRequest($_SERVER['REQUEST_METHOD'], $taskId);
