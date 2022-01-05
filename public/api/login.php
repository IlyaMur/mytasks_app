<?php

declare(strict_types=1);

use TasksApp\Gateways\TaskGateway;
use TasksApp\Gateways\UserGateway;
use TasksApp\Controllers\TaskController;
use TasksApp\Core\Database;

require dirname(__DIR__) . '/../vendor/autoload.php';
header('Content-type: application/json; charset=UTF-8');

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

// find and verify user
$user = $userGateway->getByUsername($data['username']);

if ($user === false || !password_verify((string) $data['password'], $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['message' => 'invalid authentication']);
    exit;
}

$payload = [
    'id' => $user['id'],
    'name' => $user['name']
];

$accessToken = base64_encode(json_encode($payload));

echo json_encode([
    "accessToken" => $accessToken
]);
