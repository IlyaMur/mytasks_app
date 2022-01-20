<?php


declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\TasksApp\Controllers\UserController;

class UserControllerChild extends UserController
{
    public function generateJWT(string $userId, string $username): array
    {
        return parent::generateJWT($userId, $username);
    }

    public function respondUnprocessableEntity(array $errors): void
    {
        return parent::respondUnprocessableEntity($errors);
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
