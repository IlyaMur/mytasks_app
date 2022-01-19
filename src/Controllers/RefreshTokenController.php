<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

class RefreshTokenController extends TokenController
{
    public function processRequest()
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
        $this->bodyData = (array) json_decode(file_get_contents("php://input"), true);

        if (
            !array_key_exists('refreshToken', $this->bodyData)
        ) {
            $this->respondMissingToken();

            return false;
        }

        return true;
    }

    protected function generateJWT(): void
    {
        try {
            $payload = $this->codec->decode($this->bodyData['refreshToken']);
        } catch (\Throwable) {
            $this->respondInvalidToken();
            exit;
        }

        // finding old refresh token in white list
        $refreshToken = $this->refreshTokenGateway->getByToken($this->bodyData['refreshToken']);

        if ($refreshToken === false) {
            $this->respondTokenNotInWhiteList();
            exit;
        }

        $this->user = $this->userGateway->getByID((string) $payload['sub']);

        if ($this->user === false) {
            $this->respondInvalidAuth();
            exit;
        }

        // delete old refresh token from db 
        $this->refreshTokenGateway->delete($this->bodyData['refreshToken']);

        parent::generateJWT();
    }


    public function deleteRefreshToken()
    {
        if (isset($this->bodyData['refreshToken']) && $this->refreshTokenGateway->delete($this->bodyData['refreshToken'])) {
            $this->respondTokenWasDeleted();
        } else {
            $this->respondInvalidToken();
        }
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

    protected function respondTokenNotInWhiteList(): void
    {
        http_response_code(400);
        echo json_encode(['message' => 'invalid token (not on whitelist)']);
    }
}
