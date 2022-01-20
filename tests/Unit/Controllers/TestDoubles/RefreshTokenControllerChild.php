<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

class RefreshTokenController extends RefreshTokenController
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

    public function respondMissingToken(): void
    {
        parent::respondMissingToken();
    }

    public function respondTokenNotInWhiteList(): void
    {
        parent::respondTokenNotInWhiteList();
    }

    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }
}
