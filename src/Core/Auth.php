<?php

declare(strict_types=1);

namespace TasksApp\Core;

use TasksApp\Gateways\UserGateway;
use TasksApp\Exceptions\InvalidSignatureException;
use TasksApp\Exceptions\TokenExpiredException;

class Auth
{
    private int $userId;

    public function __construct(
        private UserGateway $userGateway,
        private JWTCodec $codec
    ) {
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

        // decode JWT token and catching exception if its incorrect
        try {
            $data = $this->codec->decode($matches[1]);
        } catch (InvalidSignatureException) {
            $this->respondWarnMessage('invalid signature', 401);

            return false;
        } catch (TokenExpiredException) {
            $this->respondWarnMessage('token has expired', 401);

            return false;
        } catch (\Exception $e) {
            $this->respondWarnMessage($e->getMessage(), 400);

            return false;
        }

        $this->userId = $data['sub'];

        return true;
    }

    public function respondWarnMessage(string $msg, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode(['message' => $msg]);
    }
}
