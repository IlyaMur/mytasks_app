<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;

class UserController
{
    public function __construct(
        protected UserGateway $userGateway,
        protected RefreshTokenGateway $refreshTokenGateway,
        protected JWTCodec $codec,
        protected string $method,
        protected array $bodyData
    ) {
    }

    public function processRequest(): void
    {
        if ($this->checkMethod() && $this->validateInputData()) {
            $this->respondJWT();
        }
    }

    protected function validateInputData(): bool
    {
        $errors = $this->getValidationErrors();

        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return false;
        }

        return true;
    }

    protected function checkMethod(): bool
    {
        if ($this->method !== 'POST') {
            $this->respondMethodNotAllowed('POST');
            return false;
        }

        return true;
    }

    protected function respondJWT(): void
    {
        [$apiKey, $userId] = $this->userGateway->create($this->bodyData);

        if (!$apiKey) {
            $this->respondUnprocessableEntity(['userReg' => "Server can't handle the request"]);
            return;
        }

        // checking if JWT auth is enabled 
        if (JWT_AUTH) {
            $JWTtokens = $this->generateJWT((string) $userId);
            $this->respondCreated($JWTtokens);
        } else {
            $this->respondCreated(['accessToken' => $apiKey]);
        }
    }

    protected function getValidationErrors(): array
    {
        $errors = [];

        if (empty($this->bodyData['username'])) {
            $errors['username'] = 'Please input your username';
        }

        if (empty($this->bodyData['email'])) {
            $errors['email'] = 'Please input your email';
        } else {
            if (!filter_var($this->bodyData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please input correct email';
            } else {
                if ($this->userGateway->getByEmail($this->bodyData['email'])) {
                    $errors['email'] = 'User with this email already exists';
                }
            }
        }

        if (empty($this->bodyData['password'])) {
            $errors['password'] = 'Please input your password';
        }

        return $errors;
    }

    protected function generateJWT(string $userId): array
    {
        $payload = [
            'sub' => $userId,
            'name' => (string) $this->bodyData['username'],
            'exp' => time() + ACCESS_TOKEN_LIFESPAN
        ];

        $refreshTokenExpiry = time() + 60 * 60 * 24 * REFRESH_TOKEN_LIFESPAN;

        $accessToken = $this->codec->encode($payload);
        $refreshToken = $this->codec->encode([
            'sub' => $userId,
            'exp' => $refreshTokenExpiry
        ]);

        $this->refreshTokenGateway->create($refreshToken, $refreshTokenExpiry);

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ];
    }

    protected function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    protected function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        $this->renderJSON($errors);
    }

    protected function respondCreated(array $tokens): void
    {
        http_response_code(201);
        $this->renderJSON($tokens);
    }

    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }

    protected function getFromRequestBody(): array
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }
}
