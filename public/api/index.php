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

require dirname(__DIR__) . '/../vendor/autoload.php';
header('Content-Type: application/json; charset=UTF-8');

$parts = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$userGateway = new UserGateway($db);

$resource = $parts[2];
// selecting an endpoint based on the requested resource
switch ($resource) {
    case 'signup':
        // endpoint for signup - create new user/generate access tokens 
        $userController = new UserController(
            userGateway: $userGateway,
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $userController->processRequest();
        break;

    case 'login':
        // endpoint for login - generating new access token
        $tokenController = new TokenController(
            userGateway: $userGateway,
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)

        );
        $tokenController->processRequest();
        break;

    case 'logout':
        // endpoint for deleting existing refresh token
        $refreshTokenController = new RefreshTokenController(
            userGateway: $userGateway,
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $refreshTokenController->deleteRefreshToken();
        break;

    case 'refresh':
        // endpoint for refreshing access token by refresh token
        $refreshTokenController = new RefreshTokenController(
            userGateway: $userGateway,
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)

        );
        $refreshTokenController->processRequest();
        break;

    case 'tasks':
        // RESTful endpoint for tasks manipulating 
        $auth = new Auth($userGateway, new JWTCodec(SECRET_KEY));
        if (!$auth->authenticate()) {
            exit;
        }
        $taskController = new TaskController(taskGateway: new TaskGateway($db), userId: $auth->getUserID());
        $taskId = empty($parts[3]) ? null : $parts[3];

        $taskController->processRequest($_SERVER['REQUEST_METHOD'], $taskId);
        break;

    default:
        http_response_code(404);
}
