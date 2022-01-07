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

header('Content-Type: application/json; charset=UTF-8');

require dirname(__DIR__) . '/../vendor/autoload.php';

$parts = explode(
    '/',
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$userGateway = new UserGateway($db);
$refreshTokenGateway = new RefreshTokenGateway($db, SECRET_KEY);

$resource = $parts[2];

// selecting endpoint based on the requested resource
switch ($resource) {
    case 'login':
        // endpoint for generating access token
        $tokenController = new TokenController(
            bodyData: (array) json_decode(file_get_contents("php://input"), true),
            userGateway: $userGateway,
            refreshTokenGateway: $refreshTokenGateway,
            method: $_SERVER['REQUEST_METHOD']
        );
        $tokenController->processInputData();
        exit;

    case 'logout':
        // endpoint for deleting existing refresh token
        $refreshTokenController = new RefreshTokenController(
            bodyData: (array) json_decode(file_get_contents("php://input"), true),
            refreshTokenGateway: $refreshTokenGateway,
            userGateway: $userGateway,
            method: $_SERVER['REQUEST_METHOD']
        );
        $refreshTokenController->deleteRefreshToken();
        exit;

    case 'refresh':
        // endpoint for refreshing access token by refresh token
        $refreshTokenController = new RefreshTokenController(
            bodyData: (array) json_decode(file_get_contents("php://input"), true),
            refreshTokenGateway: $refreshTokenGateway,
            userGateway: $userGateway,
            method: $_SERVER['REQUEST_METHOD']
        );
        $refreshTokenController->processInputData();
        exit;

    case 'tasks':
        // endpoint for tasks manipulating 
        // RESTful endpoint with auth
        $auth = new Auth($userGateway, new JWTCodec(SECRET_KEY));

        // selecting type of auth (JWT token or basic API key)
        // $isAuthCorrect = JWT_AUTH ?
        //     $auth->authenticateAccessToken() :
        //     $auth->authenticateAPIKey();

        $isAuthCorrect = true;
        if (!$isAuthCorrect) {
            exit;
        }

        $taskGateway = new TaskGateway($db);
        $taskController = new TaskController(taskGateway: $taskGateway, userId: 50);

        $taskId = empty($parts[3]) ? null : $parts[3];
        $taskController->processRequest($_SERVER['REQUEST_METHOD'], $taskId);
        exit;

    default:
        http_response_code(404);
}
