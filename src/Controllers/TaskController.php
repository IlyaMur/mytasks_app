<?php

declare(strict_types=1);

namespace TasksApp\Controllers;

use TasksApp\Gateways\TaskGateway;

class TaskController
{
    public function __construct(
        private TaskGateway $taskGateway,
        private string $userId
    ) {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if (is_null($id)) {
            if ($method === 'GET') {
                echo json_encode($this->taskGateway->getAllForUser($this->userId));
            } elseif ($method === 'POST') {
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }

                $id = $this->taskGateway->createForUser($data, $this->userId);
                $this->respondCreated($id);
            } else {
                $this->respondMethodNotAllowed('GET, POST');
            }
        } else {
            // process if user provided task id
            $task = $this->taskGateway->getForUser($id, $this->userId);

            if ($task === false) {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                case 'GET':
                    echo json_encode($task);

                    break;
                case 'PATCH':
                    $data = (array) json_decode(file_get_contents("php://input"), true);

                    $errors = $this->getValidationErrors($data);

                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $rows = $this->taskGateway->updateForUser($id, $data, $this->userId);

                    echo json_encode(
                        ['message' => 'Task updated', 'rows' => $rows]
                    );

                    break;
                case "DELETE":
                    $rows = $this->taskGateway->deleteForUser($id, $this->userId);
                    echo json_encode(['message' => 'Task deleted', 'rows' => $rows]);

                    break;
                default:
                    $this->respondMethodNotAllowed('GET, PATCH, DELETE');
            }
        }
    }

    public function getValidationErrors(array $data): array
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

    private function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(['message' => "Task with ID $id not found"]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(['errors' => $errors]);
    }
}
