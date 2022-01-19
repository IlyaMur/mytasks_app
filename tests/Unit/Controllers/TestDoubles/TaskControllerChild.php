<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\TasksApp\Controllers\TaskController;

class TaskControllerChild extends TaskController
{
    public function requestToSingleEntity(): void
    {
        parent::requestToSingleEntity();
    }

    public function requestToResource(): void
    {
        parent::requestToResource();
    }

    public function processUpdateRequest(): ?array
    {
        return parent::requestToResource();
    }

    public function getValidationErrors(array $data): array
    {
        return parent::getValidationErrors($data);
    }
}
