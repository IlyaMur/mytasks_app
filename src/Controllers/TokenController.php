<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;

/**
 * TokenController
 *
 * PHP version 8.0
 */
class TokenController
{
    /**
     * Class constructor. Set parameters to token object
     *
     * @param UserGateway $userGateway UserGateway object
     * @param RefreshTokenGateway $refreshTokenGateway RefreshTokenGateway object
     * @param JWTCodec $codec JWT handler
     * @param array $bodyData Data from requests body
     * @param string $method HTTP method
     *
     * @return void
     */
    public function __construct(
        protected string $method,
        protected UserGateway $userGateway,
        protected RefreshTokenGateway $refreshTokenGateway,
        protected JWTCodec $codec,
        protected array $bodyData
    ) {
    }

    /**
     * Process the request to generate JWT
     *
     * @return void
     */
    public function processRequest()
    {
        if (
            $this->checkMethod('POST') &&
            $this->validateInputData() &&
            $this->checkUserCredentials()
        ) {
            $this->generateJWT();
        }
    }

    /**
     * Checking HTTP method
     *
     * @param string $method Allowed method
     *
     * @return bool
     */
    protected function checkMethod(string $method): bool
    {
        if ($this->method !== $method) {
            $this->respondMethodNotAllowed($method);

            return false;
        }

        return true;
    }

    /**
     * Checking input JSON.
     *
     * @return bool Return true if 'email' and 'password keys exist, false otherwise
     */
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

    /**
     * Checking user credentials: password and email
     *
     * @return bool Return true if credentials are valid, false otherwise
     */
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

    /**
     * Generate JWT
     *
     * @return void
     */
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

    /**
     * Respond JWT
     *
     * @param array $tokens array with JWT
     *
     * @return void
     */
    protected function respondTokens(array $tokens): void
    {
        $this->renderJSON($tokens);
    }

    /**
     * Respond invalid auth message and set 401 status code
     *
     * @return void
     */
    protected function respondInvalidAuth(): void
    {
        http_response_code(401);
        $this->renderJSON(['general' => 'No user with this data was found']);
    }

    /**
     * Respond JWT was succefully deleted
     *
     * @return void
     */
    protected function respondTokenWasDeleted(): void
    {
        http_response_code(200);
        $this->renderJSON(['message' => 'Token was deleted']);
    }

    /**
     * Respond missing credentials
     *
     * @return void
     */
    protected function respondMissingCredentials(): void
    {
        http_response_code(400);
        $this->renderJSON(['general' => 'missing login credentials']);
    }

    /**
     * Respond http method not allowed
     * Sending header with allowed method
     *
     * @return void
     */
    protected function respondMethodNotAllowed(string $method): void
    {
        http_response_code(405);
        header("Allow: $method");
    }

    /**
     * Render JSON
     *
     * @param mixed Array or string
     *
     * @return void
     */
    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }
}
