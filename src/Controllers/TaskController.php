<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Controllers;

use Ilyamur\TasksApp\Gateways\TaskGateway;

/**
 * TaskController
 *
 * PHP version 8.0
 */
class TaskController
{
    /**
     * Class constructor. Set parameter to task object
     *
     * @param TaskGateway $taskGateway TaskGateway object
     * @param string $userId (optional) A token value
     * @param string $method (optional) A token value
     * @param mixed $taskId Can be null if request was not to single entity
     *
     * @return void
     */
    public function __construct(
        private TaskGateway $taskGateway,
        private string $userId,
        private string $method,
        private ?string $taskId,
    ) {
    }

    /**
     * Process the request to tasks
     * Selecting type of request depending on presence of taskId
     *
     * @return void
     */
    public function processRequest(): void
    {
        is_null($this->taskId) ? $this->requestToResource() : $this->requestToSingleEntity();
    }

    /**
     * Process the request to single task
     *
     * @return void
     */
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

    /**
     * Process the request to resource
     *
     * @return void
     */
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

    /**
     * Process task update request
     *
     * @return void
     */
    protected function processUpdateRequest(): void
    {
        $data = $this->getFromRequestBody();
        if (!$this->validateData($data)) {
            return;
        }

        $rows = $this->taskGateway->updateForUser($this->taskId, $data, $this->userId);

        $this->renderJSON(['message' => 'Task updated', 'rows' => $rows]);
    }

    /**
     * Process task create request
     *
     * @return void
     */
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

    /**
     * Validating input task data
     *
     * @return bool
     */
    protected function validateData(array $data): bool
    {
        $errors = $this->getValidationErrors($data);

        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return false;
        }

        return true;
    }

    /**
     * Process delete request
     *
     * @return void
     */
    protected function processDeleteRequest()
    {
        $rows = $this->taskGateway->deleteForUser($this->taskId, $this->userId);
        $this->renderJSON(['message' => 'Task deleted', 'rows' => $rows]);
    }

    /**
     * Get validation errors from input task data
     *
     * @param array $data Input task data
     *
     * @return array Validation errors
     */
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

    /**
     * Respond method not allowed
     * Sending header with allowed methods
     *
     * @return void
     */
    protected function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    /**
     * Respond method not found
     *
     * @return void
     */
    protected function respondNotFound(): void
    {
        http_response_code(404);
        $this->renderJSON(['message' => "Task with ID $this->taskId not found"]);
    }

    /**
     * Respond task was created
     *
     * @param string $newTaskId New Task id
     *
     * @return void
     */
    protected function respondCreated(string $newTaskId): void
    {
        http_response_code(201);
        $this->renderJSON(["message" => "Task created", "id" => $newTaskId]);
    }

    /**
     * Respond unprocessable entity
     *
     * @param array $errors Errors
     *
     * @return void
     */
    protected function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        $this->renderJSON(['errors' => $errors]);
    }

    /**
     * Render JSON
     *
     * @param mixed Array or string
     *
     * @return void
     */
    protected function renderJSON(array | string $item): void
    {
        echo json_encode($item);
    }

    /**
     * Parse JSON from requests body
     *
     * @param mixed Array or string
     *
     * @return array Array of requests body data
     */
    public function getFromRequestBody(): array
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }
}
