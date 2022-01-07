<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\UserGateway;
use TasksApp\Gateways\RefreshTokenGateway;

class TokenController
{
    public function __construct(
        protected array $bodyData,
        protected string $method,
        protected UserGateway $userGateway,
        protected RefreshTokenGateway $refreshTokenGateway
    ) {
    }

    public function processInputData()
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
        if (
            !array_key_exists('username', $this->bodyData) ||
            !array_key_exists('password', $this->bodyData)
        ) {
            $this->respondMissingCredentials();

            return false;
        }

        return true;
    }

    protected function checkUserCredentials(): bool
    {
        $this->user = $this->userGateway->getByUsername($this->bodyData['username']);

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
            'name' => $this->user['name'],
            'exp' => time() + 20
        ];

        $codec = new JWTCodec(SECRET_KEY);
        $refreshTokenExpiry = time() + 60 * 60 * 24 * 5;

        $accessToken = $codec->encode($payload);
        $refreshToken = $codec->encode([
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
        echo json_encode(['message' => 'invalid authentication']);
    }

    protected function respondTokenWasDeleted(): void
    {
        http_response_code(200);
        echo json_encode(['message' => 'Token was deleted']);
    }

    protected function respondMissingCredentials(): void
    {
        http_response_code(400);
        echo json_encode(['message' => 'missing login credentials']);
    }

    protected function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }
}
