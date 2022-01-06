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
            $this->respondWarnMessage('missing API key');

            return false;
        };

        $apiKey = $_SERVER['HTTP_X_API_KEY'];

        $user = $this->userGateway->getByAPIKey($apiKey);

        if ($user === false) {
            $this->respondWarnMessage('invalid API key', 401);

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
        // check if Bearer type persist in the beginning of auth header
        if (!preg_match("/^Bearer\s+(.*)$/", $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $this->respondWarnMessage('incomplete authorization header');

            return false;
        }

        $plainText = base64_decode($matches[1], true);
        // if token can't be decoded
        if ($plainText === false) {
            $this->respondWarnMessage('invalid authorization header');

            return false;
        }

        $jsonData = json_decode($plainText, true);
        // if input JSON can't be decoded
        if ($jsonData === null) {
            $this->respondWarnMessage('invalid JSON');

            return false;
        }
        $this->userId = $jsonData['id'];

        return true;
    }

    public function respondWarnMessage(string $msg, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode(['message' => $msg]);
    }
}
