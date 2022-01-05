<?php

declare(strict_types=1);

namespace TasksApp\Core;

use TasksApp\Gateways\UserGateway;

class Auth
{
    private int $userId;

    public function __construct(private UserGateway $userGateway)
    {
    }

    public function authenticateAPIKey(): bool
    {
        if (empty($_SERVER['HTTP_X_API_KEY'])) {
            http_response_code(400);
            echo json_encode(['message' => 'missing API key']);
            return false;
        };

        $apiKey = $_SERVER['HTTP_X_API_KEY'];

        $user = $this->userGateway->getByAPIKey($apiKey);

        if ($user === false) {
            http_response_code(401);
            echo json_encode(['message' => 'invalid API key']);
            return false;
        }

        $this->userId = $user['id'];

        return true;
    }

    public function getUserID(): int
    {
        return $this->userId;
    }

    public function authenticateAccessToken(): bool
    {
        // check if Bearer type in auth header
        if (!preg_match("/^Bearer\s+(.*)$/", $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            http_response_code(400);
            echo json_encode(['message' => 'incomplete authorization header']);
            return false;
        }

        var_dump($matches[1]);
    }
}
