<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

/**
 * RefreshTokenController
 *
 * PHP version 8.0
 */
class RefreshTokenController extends TokenController
{
    /**
     * Process the request to generate JWT
     *
     * @return void
     */
    public function processRequest(): void
    {
        if (
            $this->checkMethod('POST') &&
            $this->validateInputData()
        ) {
            $this->generateJWT();
        }
    }

    /**
     * Checking input JSON.
     *
     * @return bool Return true if 'refreshToken' key exists, false otherwise
     */
    protected function validateInputData(): bool
    {
        if (!array_key_exists('refreshToken', $this->bodyData)) {
            $this->respondMissingToken();

            return false;
        }

        return true;
    }

    /**
     * Decode input JWT and generate new one
     *
     * @return void
     */
    protected function generateJWT(): void
    {
        try {
            $payload = $this->codec->decode($this->bodyData['refreshToken']);
        } catch (\Throwable) {
            $this->respondInvalidToken();
            return;
        }

        // Finding old refresh token in white list
        $refreshToken = $this->refreshTokenGateway->getByToken($this->bodyData['refreshToken']);

        if ($refreshToken === false) {
            $this->respondTokenNotInWhiteList();
            return;
        }

        $this->user = $this->userGateway->getByID((string) $payload['sub']);

        if ($this->user === false) {
            $this->respondInvalidAuth();
            return;
        }

        // Delete old refresh token from DB
        $this->refreshTokenGateway->delete($this->bodyData['refreshToken']);

        parent::generateJWT();
    }

    /**
     * Deleting Refresh Token for logout request
     *
     * @return void
     */
    public function deleteRefreshToken(): void
    {
        if (!$this->checkMethod('DELETE')) {
            return;
        }

        if (
            isset($this->bodyData['refreshToken']) &&
            $this->refreshTokenGateway->delete($this->bodyData['refreshToken'])
        ) {
            $this->respondTokenWasDeleted();
        } else {
            $this->respondInvalidToken();
        }
    }

    /**
     * Respond invalid auth message and set 401 status code
     *
     * @return void
     */
    protected function respondInvalidAuth(): void
    {
        http_response_code(401);
        $this->renderJSON(['message' => 'invalid authentication']);
    }

    /**
     * Respond invalid token message and set 401 status code
     *
     * @return void
     */
    protected function respondInvalidToken(): void
    {
        http_response_code(401);
        $this->renderJSON(['message' => 'invalid token']);
    }

    /**
     * Respond missing token message and set 400 status code
     *
     * @return void
     */
    protected function respondMissingToken(): void
    {
        http_response_code(400);
        $this->renderJSON(['message' => 'missing token']);
    }

    /**
     * Respond if token not in white list and set 400 status code
     *
     * @return void
     */
    protected function respondTokenNotInWhiteList(): void
    {
        http_response_code(400);
        $this->renderJSON(['message' => 'invalid token (not on whitelist)']);
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
