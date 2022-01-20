<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\TasksApp\Controllers\RefreshTokenController;

class RefreshTokenControllerChild extends RefreshTokenController
{
    public function validateInputData(): bool
    {
        return parent::validateInputData();
    }

    public function generateJWT(): void
    {
        parent::generateJWT();
    }

    public function deleteRefreshToken(): void
    {
        parent::deleteRefreshToken();
    }

    public function respondInvalidAuth(): void
    {
        parent::respondInvalidAuth();
    }

    public function respondInvalidToken(): void
    {
        parent::respondInvalidToken();
    }

    public function respondMissingToken(): void
    {
        parent::respondMissingToken();
    }

    public function respondTokenNotInWhiteList(): void
    {
        parent::respondTokenNotInWhiteList();
    }

    public function renderJSON($item): void
    {
        parent::renderJSON($item);
    }
}
