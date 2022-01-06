<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Core\JWTCodec;
use TasksApp\Gateways\UserGateway;

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

    public function validateInputData(): bool
    {
        if (
            !array_key_exists('token', $this->bodyData)
        ) {
            $this->respondMissingToken();

            return false;
        }

        return true;
    }

    public function generateJWT(): void
    {
        $codec = new JWTCodec(SECRET_KEY);

        try {
            $payload = $codec->decode($this->bodyData['token']);
        } catch (\Throwable $th) {
            $this->respondInvalidToken();
            exit;
        }

        $userId = $payload['sub'];
        $user = $this->userGateway->getByID($userId);

        if ($user === false) {
            $this->respondInvalidAuth();
            exit;
        }
        var_dump($user);


        // $accessToken = $codec->encode($payload);
        // $refreshToken = $codec->encode([
        //     'sub' => $this->user['id'],
        //     'exp' => time() + 60 * 60 * 24 * 5
        // ]);

        // echo json_encode(
        //     [
        //         'accessToken' => $accessToken,
        //         'refreshToken' => $refreshToken
        //     ]
        // );
    }

    public function respondInvalidAuth(): void
    {
        http_response_code(401);
        echo json_encode(['message' => 'invalid authentication']);
    }

    public function respondInvalidToken(): void
    {
        http_response_code(401);
        echo json_encode(['message' => 'invalid token']);
    }

    public function respondMissingToken(): void
    {
        http_response_code(400);
        echo json_encode(['message' => 'missing token']);
    }

    public function respondMethodNotAllowed(): void
    {
        http_response_code(405);
        header('Allow: POST');
    }
}
