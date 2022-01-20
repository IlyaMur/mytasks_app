<?php

namespace Ilyamur\TaskApp\Tests\Unit\Services\TestDoubles;

use Ilyamur\TasksApp\Services\Auth;

class AuthChild extends Auth
{
    public function authenticateAPIKey(): bool
    {
        return parent::authenticateAPIKey();
    }

    public function authenticateAccessToken(): bool
    {
        return parent::authenticateAccessToken();
    }

    public function respondWarnMessage(string $msg, int $statusCode = 400): void
    {
        parent::respondWarnMessage($msg, $statusCode);
    }

    public function renderJSON(array | string $item): void
    {
        parent::respondWarnMessage($item);
    }
}
