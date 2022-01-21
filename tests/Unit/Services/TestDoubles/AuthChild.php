<?php

namespace Ilyamur\TaskApp\Tests\Unit\Services\TestDoubles;

use Ilyamur\TasksApp\Services\Auth;

class AuthChild extends Auth
{
    public function authenticateByKey(): bool
    {
        return parent::authenticateByKey();
    }

    public function authenticateByJWT(): bool
    {
        return parent::authenticateByJWT();
    }

    public function respondWarnMessage(string $msg, int $statusCode = 400): void
    {
        parent::respondWarnMessage($msg, $statusCode);
    }

    public function renderJSON(array | string $item): void
    {
        parent::renderJSON($item);
    }
}
