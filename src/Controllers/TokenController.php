<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\UserGateway;

class TokenController
{
    public function __construct(
        private array $bodyData,
        private string $method,
        private UserGateway $userGateway
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

    public function validateInputData(): bool
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

    public function checkUserCredentials(): bool
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

    public function generateJWT(): void
    {
        $payload = [
            'sub' => $this->user['id'],
            'name' => $this->user['name'],
            'exp' => time() + 20
        ];

        $codec = new JWTCodec(SECRET_KEY);
        $accessToken = $codec->encode($payload);
        $refreshToken = $codec->encode([
            'sub' => $this->user['id'],
            'exp' => time() + 60 * 60 * 24 * 5
        ]);

        echo json_encode(
            [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken
            ]
        );
    }

    public function respondInvalidAuth(): void
    {
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
    }

    public function respondMissingCredentials(): void
    {
        http_response_code(400);
        echo json_encode(['message' => 'missing login credentials']);
    }

    public function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }
}
