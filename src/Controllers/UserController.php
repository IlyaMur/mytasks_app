<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\UserGateway;
use TasksApp\Gateways\RefreshTokenGateway;

class UserController
{
    public function __construct(
        private UserGateway $userGateway,
        private RefreshTokenGateway $refreshTokenGateway
    ) {
    }

    public function processRequest(string $method): void
    {
        if ($method !== 'POST') {
            $this->respondMethodNotAllowed('POST');
            return;
        }

        $data = (array) json_decode(file_get_contents("php://input"), true);
        $errors = $this->validateInput($data);

        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return;
        }

        [$apiKey, $userId] = $this->userGateway->create($data);

        if (!$apiKey) {
            $this->respondUnprocessableEntity(['userReg' => "Server can't handle the request"]);
            return;
        }

        if (JWT_AUTH) {
            $JWTtokens = $this->generateJWT($userId, $data['username']);
            $this->respondCreated($JWTtokens);
        } else {
            $this->respondCreated(['apiKey' => $apiKey]);
        }
    }

    public function validateInput($userData): array
    {
        $errors = [];

        if (empty($userData['username'])) {
            $errors['username'] = 'Please input your username';
        }

        if (empty($userData['email'])) {
            $errors['email'] = 'Please input your email';
        } else {
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please input correct email';
            } else {
                if ($this->userGateway->getByEmail($userData['email'])) {
                    $errors['email'] = 'User with this email already exists';
                }
            }
        }

        if (empty($userData['password'])) {
            $errors['password'] = 'Please input your password';
        }

        return $errors;
    }

    private function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode($errors);
    }

    private function respondCreated(array $tokens): void
    {
        http_response_code(201);
        echo json_encode($tokens);
    }

    protected function generateJWT(string $userId, string $username): array
    {
        $payload = [
            'sub' => $userId,
            'name' => $username,
            'exp' => time() + ACCESS_TOKEN_LIFESPAN
        ];

        $codec = new JWTCodec(SECRET_KEY);
        $refreshTokenExpiry = time() + 60 * 60 * 24 * REFRESH_TOKEN_LIFESPAN;

        $accessToken = $codec->encode($payload);
        $refreshToken = $codec->encode([
            'sub' => $userId,
            'exp' => $refreshTokenExpiry
        ]);

        $this->refreshTokenGateway->create($refreshToken, $refreshTokenExpiry);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ];
    }
}
