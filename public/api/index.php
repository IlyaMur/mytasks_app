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
use TasksApp\Controllers\UserController;

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

// selecting an endpoint based on the requested resource
switch ($resource) {
    case 'signup':
        // endpoint for signup - create new user/generate access tokens 
        $userController = new UserController(
            userGateway: $userGateway,
            refreshTokenGateway: $refreshTokenGateway,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $userController->processRequest();
        break;

    case 'login':
        // endpoint for login - generating new access token
        $tokenController = new TokenController(
            userGateway: $userGateway,
            refreshTokenGateway: $refreshTokenGateway,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)

        );
        $tokenController->processInputData();
        break;

    case 'logout':
        // endpoint for deleting existing refresh token
        $refreshTokenController = new RefreshTokenController(
            userGateway: $userGateway,
            refreshTokenGateway: $refreshTokenGateway,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $refreshTokenController->deleteRefreshToken();
        break;

    case 'refresh':
        // endpoint for refreshing access token by refresh token
        $refreshTokenController = new RefreshTokenController(
            refreshTokenGateway: $refreshTokenGateway,
            userGateway: $userGateway,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)

        );
        $refreshTokenController->processInputData();
        break;

    case 'tasks':
        // endpoint for tasks manipulating 
        // RESTful endpoint with auth
        $auth = new Auth($userGateway, new JWTCodec(SECRET_KEY));

        // selecting type of auth (JWT token or basic API key)
        $isAuthCorrect = JWT_AUTH ?
            $auth->authenticateAccessToken() :
            $auth->authenticateAPIKey();

        if (!$isAuthCorrect) {
            exit;
        }
        $taskGateway = new TaskGateway($db);
        $taskController = new TaskController(taskGateway: $taskGateway, userId: $auth->getUserID());

        $taskId = empty($parts[3]) ? null : $parts[3];
        $taskController->processRequest($_SERVER['REQUEST_METHOD'], $taskId);
        break;

    default:
        http_response_code(404);
}
