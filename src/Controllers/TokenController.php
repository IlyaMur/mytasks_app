<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

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
            $this->printToken();
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
            !password_verify((string) $this->bodyData['password'], $this->user['password_hash'])
        ) {
            $this->respondInvalidAuth();

            return false;
        }

        return true;
    }

    public function printToken(): void
    {
        echo base64_encode(
            json_encode(['id' => $this->user['id'], 'name' => $this->user['name']])
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
