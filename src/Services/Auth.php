<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Services;

use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Exceptions\InvalidSignatureException;
use Ilyamur\TasksApp\Exceptions\TokenExpiredException;

/**
 * Auth
 *
 * PHP version 8.0
 */
class Auth
{
    /**
     * User ID
     *
     * @var string
     */
    private string $userId;

    /**
     * Class constructor
     *
     * @param UserGateway $userGateway Usergateway object
     * @param JWTCodec $codec JWTCodec object
     *
     * @return void
     */
    public function __construct(
        private UserGateway $userGateway,
        private JWTCodec $codec
    ) {
    }

    /**
     * Authenticate user
     *
     * @return bool
     */
    public function authenticate(): bool
    {
        // Selecting type of auth (JWT token or basic API key)
        // Adjusting in the config file
        return JWT_AUTH ? $this->authenticateByJWT() : $this->authenticateByKey();
    }

    /**
     * Get user ID
     *
     * @return string
     */
    public function getUserID(): string
    {
        return $this->userId;
    }

    /**
     * Authenticate user by API key
     *
     * @return bool
     */
    protected function authenticateByKey(): bool
    {
        $apiKey = $this->getAPIKeyFromHeader();

        if (!$apiKey) {
            $this->respondWarnMessage('missing API key');
            return false;
        };

        $user = $this->userGateway->getByAPIKey($apiKey);

        if ($user === false) {
            $this->respondWarnMessage('invalid API key', 401);
            return false;
        }
        $this->userId = (string) $user['id'];

        return true;
    }

    /**
     * Authenticate user by JWT
     *
     * @return bool
     */
    protected function authenticateByJWT(): bool
    {
        // Check if Bearer key persist in the beginning of auth header
        if (!preg_match("/^Bearer\s+(.*)$/", $this->getJWTFromHeader() ?? '', $matches)) {
            $this->respondWarnMessage('incomplete authorization header');

            return false;
        }

        // Decode JWT token and catching exception if its incorrect
        try {
            $data = $this->codec->decode($matches[1]);
        } catch (InvalidSignatureException) {
            $this->respondWarnMessage('invalid signature', 401);

            return false;
        } catch (TokenExpiredException) {
            $this->respondWarnMessage('token has expired', 401);

            return false;
        } catch (\Exception $e) {
            $this->respondWarnMessage($e->getMessage(), 400);

            return false;
        }
        $this->userId = (string) $data['sub'];

        return true;
    }

    /**
     * Get API key from X-API-KEY header
     *
     * @return mixed
     */
    protected function getAPIKeyFromHeader(): ?string
    {
        return empty($_SERVER['HTTP_X_API_KEY']) ? null : $_SERVER['HTTP_X_API_KEY'];
    }

    /**
     * Get JWT from AUTHORIZATION header
     *
     * @return mixed
     */
    protected function getJWTFromHeader(): ?string
    {
        return empty($_SERVER['HTTP_AUTHORIZATION']) ? null : $_SERVER['HTTP_AUTHORIZATION'];
    }

    /**
     * Respond message
     *
     * @param string $msg Message
     * @param int $statusCode response status code
     *
     * @return void
     */
    protected function respondWarnMessage(string $msg, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        $this->renderJSON(['message' => $msg]);
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
