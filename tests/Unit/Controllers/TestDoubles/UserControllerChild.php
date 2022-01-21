<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\TasksApp\Controllers\UserController;

class UserControllerChild extends UserController
{
    public function validateInputData(): bool
    {
        return parent::validateInputData();
    }

    public function respondJWT(): void
    {
        parent::respondJWT();
    }

    public function checkMethod(): bool
    {
        return parent::checkMethod();
    }

    public function getValidationErrors(): array
    {
        return parent::getValidationErrors();
    }

    public function generateJWT(string $userId): array
    {
        return parent::generateJWT($userId);
    }

    public function respondUnprocessableEntity(array $errors): void
    {
        parent::respondUnprocessableEntity($errors);
    }

    public function respondCreated(array $tokens): void
    {
        parent::respondCreated($tokens);
    }

    public function renderJSON(array | string $item): void
    {
        parent::renderJSON($item);
    }
}
