<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\UserGateway;
use TasksApp\Gateways\RefreshTokenGateway;

class TokenController
{
    public function __construct(
        protected string $method,
        protected UserGateway $userGateway,
        protected RefreshTokenGateway $refreshTokenGateway,
        protected JWTCodec $codec
    ) {
    }

    public function processRequest()
    {
        if (
            $this->checkMethod() &&
            $this->validateInputData() &&
            $this->checkUserCredentials()
        ) {
            $this->generateJWT();
        }
    }

    public function checkMethod(): bool
    {
        if ($this->method !== 'POST') {
            $this->respondMethodNotAllowed();

            return false;
        }

        return true;
    }

    protected function validateInputData(): bool
    {
        $this->bodyData = (array) json_decode(file_get_contents("php://input"), true);

        if (
            !array_key_exists('email', $this->bodyData) ||
            !array_key_exists('password', $this->bodyData)
        ) {
            $this->respondMissingCredentials();

            return false;
        }

        return true;
    }

    protected function checkUserCredentials(): bool
    {
        $this->user = $this->userGateway->getByEmail($this->bodyData['email']);

        if (
            !$this->user ||
            !password_verify(
                (string) $this->bodyData['password'],
                $this->user['password_hash']
            )
        ) {
            $this->respondInvalidAuth();

            return false;
        }

        return true;
    }

    protected function generateJWT(): void
    {
        $payload = [
            'sub' => $this->user['id'],
            'name' => $this->user['username'],
            'exp' => time() + ACCESS_TOKEN_LIFESPAN
        ];

        $refreshTokenExpiry = time() + 60 * 60 * 24 * REFRESH_TOKEN_LIFESPAN;

        $accessToken = $this->codec->encode($payload);
        $refreshToken = $this->codec->encode([
            'sub' => $this->user['id'],
            'exp' => $refreshTokenExpiry
        ]);

        echo json_encode(
            [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken
            ]
        );

        $this->refreshTokenGateway->create($refreshToken, $refreshTokenExpiry);
    }

    protected function respondInvalidAuth(): void
    {
        http_response_code(401);
        echo json_encode(['general' => 'No user with this data was found']);
    }

    protected function respondTokenWasDeleted(): void
    {
        http_response_code(200);
        echo json_encode(['message' => 'Token was deleted']);
    }

    protected function respondMissingCredentials(): void
    {
        http_response_code(400);
        echo json_encode(['general' => 'missing login credentials']);
    }

    protected function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }
}
