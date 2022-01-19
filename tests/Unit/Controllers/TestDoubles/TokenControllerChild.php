<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\TasksApp\Controllers\TokenController;

class TokenControllerChild extends TokenController
{
    public function checkMethod(): bool
    {
        return parent::checkMethod();
    }

    public function validateInputData(): bool
    {
        return parent::validateInputData();
    }

    public function checkUserCredentials(): bool
    {
        return parent::checkUserCredentials();
    }

    public function generateJWT(): void
    {
        parent::generateJWT();
    }
}
