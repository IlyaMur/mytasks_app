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

    public function processUpdateRequest(): void
    {
        parent::processUpdateRequest();
    }

    public function processCreateRequest(): void
    {
        parent::processCreateRequest();
    }

    public function processDeleteRequest(): void
    {
        parent::processDeleteRequest();
    }

    public function getValidationErrors(array $data): array
    {
        return parent::getValidationErrors($data);
    }

    public function respondNotFound(): void
    {
        parent::respondNotFound();
    }

    public function respondCreated(string $newTaskId): void
    {
        parent::respondCreated($newTaskId);
    }

    public function renderJSON(array | string $item): void
    {
        parent::renderJSON($item);
    }
}
