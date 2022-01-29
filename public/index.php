<?php

declare(strict_types=1);

use Ilyamur\TasksApp\Services\Auth;
use Ilyamur\TasksApp\Services\Database;
use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\TaskGateway;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Controllers\TaskController;
use Ilyamur\TasksApp\Controllers\UserController;
use Ilyamur\TasksApp\Controllers\TokenController;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;
use Ilyamur\TasksApp\Controllers\RefreshTokenController;

/**
 * Front Controller
 * 
 * PHP version 8.0
 */
require dirname(__DIR__) . '/vendor/autoload.php';

// Filter redundant slashes and parse request URI
$reqUri = preg_replace('/(\/)+/', '/', $_SERVER['REQUEST_URI']);
$parts = explode('/', parse_url($reqUri, PHP_URL_PATH));

// Reject if it's an incorrect API request
if ($parts[1] !== 'api' || ($parts[1] === 'api' && empty($parts[2]))) {
    http_response_code(404);
    return;
}

$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
// Get JSON from the request body
$bodyData = (array) json_decode(file_get_contents("php://input"), true);

/**
 * Routing
 * Select an endpoint based on the requested resource
 */
switch ($parts[2]) {
    case 'signup':
        // Endpoint for signup - create new user/generate access tokens 
        $userController = new UserController(
            userGateway: new UserGateway($db),
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            bodyData: $bodyData,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $userController->processRequest();
        break;

    case 'login':
        // Endpoint for login - generating new access tokens
        $tokenController = new TokenController(
            userGateway: new UserGateway($db),
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            bodyData: $bodyData,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $tokenController->processRequest();
        break;

    case 'logout':
        // Endpoint for deleting existing refresh token
        $refreshTokenController = new RefreshTokenController(
            userGateway: new UserGateway($db),
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            bodyData: $bodyData,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $refreshTokenController->deleteRefreshToken();
        break;

    case 'refresh':
        // Endpoint for refreshing access token by refresh token
        $refreshTokenController = new RefreshTokenController(
            userGateway: new UserGateway($db),
            refreshTokenGateway: new RefreshTokenGateway($db, SECRET_KEY),
            bodyData: $bodyData,
            method: $_SERVER['REQUEST_METHOD'],
            codec: new JWTCodec(SECRET_KEY)
        );
        $refreshTokenController->processRequest();
        break;

    case 'tasks':
        // RESTful endpoint for tasks manipulating 
        $auth = new Auth(new UserGateway($db), new JWTCodec(SECRET_KEY));
        if (!$auth->authenticate()) {
            break;
        }
        $taskController = new TaskController(
            taskGateway: new TaskGateway($db),
            method: $_SERVER['REQUEST_METHOD'],
            taskId: empty($parts[3]) ? null : $parts[3],
            userId: $auth->getUserID()
        );
        $taskController->processRequest();
        break;

    default:
        http_response_code(404);
}
