<?php

declare(strict_types=1);

namespace TasksApp;

class TaskController
{
    public function __construct(private TaskGateway $gateway)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if (is_null($id)) {
            if ($method === 'GET') {
                echo json_encode($this->gateway->getAll());
            } elseif ($method === 'POST') {
                echo 'create';
            } else {
                $this->respondMethodNotAllowed('GET, POST');
            }
        } else {
            switch ($method) {
                case 'GET':
                    echo json_encode($this->gateway->get($id));
                    break;
                case 'PATCH':
                    echo "update $id";
                    break;
                case "DELETE":
                    echo "delete $id";
                    break;
                default:
                    $this->respondMethodNotAllowed('GET, PATCH, DELETE');
            }
        }
    }

    private function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }
}
