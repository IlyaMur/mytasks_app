<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

use Ilyamur\TasksApp\Gateways\TaskGateway;

class TaskController
{
    public function __construct(
        private TaskGateway $taskGateway,
        private string $userId,
        private string $method,
        private ?string $taskId,
    ) {
    }

    public function processRequest(): void
    {
        is_null($this->taskId) ? $this->requestToResource() : $this->requestToSingleEntity();
    }

    protected function requestToSingleEntity(): void
    {
        $task = $this->taskGateway->getForUser($this->taskId, $this->userId);

        if ($task === false) {
            $this->respondNotFound();
            return;
        }

        switch ($this->method) {
            case 'GET':
                $this->renderJSON($task);

                break;
            case 'PATCH':
                $this->processUpdateRequest();

                break;
            case 'DELETE':
                $this->processDeleteRequest();

                break;
            default:
                $this->respondMethodNotAllowed('GET, PATCH, DELETE');
        }
    }

    protected function requestToResource(): void
    {
        switch ($this->method) {
            case 'GET':
                $this->renderJSON($this->taskGateway->getAllForUser($this->userId));

                break;
            case 'POST':
                $this->processCreateRequest();

                break;
            default:
                $this->respondMethodNotAllowed('GET, POST');
        }
    }

    protected function processUpdateRequest(): void
    {
        $data = $this->getFromRequestBody();
        if (!$this->validateData($data)) {
            return;
        }

        $rows = $this->taskGateway->updateForUser($this->taskId, $data, $this->userId);

        $this->renderJSON(['message' => 'Task updated', 'rows' => $rows]);
    }

    protected function processCreateRequest(): void
    {
        $data = $this->getFromRequestBody();
        if (!$this->validateData($data)) {
            return;
        }

        $newTaskId = $this->taskGateway->createForUser($data, $this->userId);

        if ($newTaskId) {
            $this->respondCreated($newTaskId);
        }
    }

    protected function validateData(array $data): bool
    {
        $errors = $this->getValidationErrors($data);

        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return false;
        }

        return true;
    }

    protected function processDeleteRequest()
    {
        $rows = $this->taskGateway->deleteForUser($this->taskId, $this->userId);
        $this->renderJSON(['message' => 'Task deleted', 'rows' => $rows]);
    }

    protected function getValidationErrors(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'title is required';
        }

        if (empty($data['body'])) {
            $errors['body'] = 'body is required';
        }

        if (isset($data['priority'])) {
            if (filter_var($data['priority'], FILTER_VALIDATE_INT) === false) {
                $errors['priority'] = 'priority must be an integer';
            }
        }

        return $errors;
    }

    protected function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    protected function respondNotFound(): void
    {
        http_response_code(404);
        $this->renderJSON(['message' => "Task with ID $this->taskId not found"]);
    }

    protected function respondCreated(string $newTaskId): void
    {
        http_response_code(201);
        $this->renderJSON(["message" => "Task created", "id" => $newTaskId]);
    }

    protected function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        $this->renderJSON(['errors' => $errors]);
    }

    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }

    public function getFromRequestBody(): array
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }
}
