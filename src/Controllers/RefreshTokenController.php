<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;

class RefreshTokenController extends TokenController
{
    public function processInputData()
    {
        if (
            $this->checkMethod() &&
            $this->validateInputData()
        ) {
            $this->generateJWT();
        }
    }

    protected function validateInputData(): bool
    {
        if (
            !array_key_exists('token', $this->bodyData)
        ) {
            $this->respondMissingToken();

            return false;
        }

        return true;
    }

    protected function generateJWT(): void
    {
        $codec = new JWTCodec(SECRET_KEY);

        try {
            $payload = $codec->decode($this->bodyData['token']);
        } catch (\Throwable) {
            $this->respondInvalidToken();
            exit;
        }

        $this->user = $this->userGateway->getByID($payload['sub']);

        if ($this->user === false) {
            $this->respondInvalidAuth();
            exit;
        }

        // delete old refresh token from db 
        $this->refreshTokenGateway->delete($this->bodyData['token']);

        parent::generateJWT();
    }

    protected function respondInvalidAuth(): void
    {
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
    }

    protected function respondInvalidToken(): void
    {
        http_response_code(401);
        echo json_encode(['message' => 'invalid token']);
    }

    protected function respondMissingToken(): void
    {
        http_response_code(400);
        echo json_encode(['message' => 'missing token']);
    }

    protected function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }
}
