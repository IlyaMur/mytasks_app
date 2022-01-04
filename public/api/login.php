<?php

declare(strict_types=1);

use TasksApp\Gateways\TaskGateway;
use TasksApp\Gateways\UserGateway;
use TasksApp\Controllers\TaskController;
use TasksApp\Core\Database;

require dirname(__DIR__) . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$data = (array) json_decode(file_get_contents("php://input"), true);

if (
    !array_key_exists('username', $data) ||
    !array_key_exists('password', $data)
) {
    http_response_code(400);
    echo json_encode(['message' => 'missing login credentials']);
    exit;
}

$db = new Database(
    user: DB_USER,
    password: DB_PASS,
    host: DB_HOST,
    name: DB_NAME
);

$userGateway = new UserGateway($db);
$user = $userGateway->getByUsername($data['username']);

echo json_encode($user);
