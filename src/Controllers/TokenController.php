<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;

class TokenController
{
    public function __construct(
        protected string $method,
        protected UserGateway $userGateway,
        protected RefreshTokenGateway $refreshTokenGateway,
        protected JWTCodec $codec,
        protected array $bodyData
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

    protected function checkMethod(): bool
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
            'exp' => time() + ACCESS_TOKEN_LIFESPAN // tokens lifespan configuring in the config file
        ];

        $refreshTokenExpiry = time() + 60 * 60 * 24 * REFRESH_TOKEN_LIFESPAN;

        $accessToken = $this->codec->encode($payload);
        $refreshToken = $this->codec->encode([
            'sub' => $this->user['id'],
            'exp' => $refreshTokenExpiry
        ]);

        $this->respondTokens([
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ]);

        $this->refreshTokenGateway->create($refreshToken, $refreshTokenExpiry);
    }

    protected function respondTokens(array $tokens): void
    {
        $this->renderJSON($tokens);
    }

    protected function respondInvalidAuth(): void
    {
        http_response_code(401);
        $this->renderJSON(['general' => 'No user with this data was found']);
    }

    protected function respondTokenWasDeleted(): void
    {
        http_response_code(200);
        $this->renderJSON(['message' => 'Token was deleted']);
    }

    protected function respondMissingCredentials(): void
    {
        http_response_code(400);
        $this->renderJSON(['general' => 'missing login credentials']);
    }

    protected function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }

    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }
}
