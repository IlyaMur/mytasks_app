<?php

declare(strict_types=1);

namespace TasksApp;

class TaskController
{
    public function processRequest(string $method, ?string $id): void
    {
        if (is_null($id)) {
            if ($method === 'GET') {
                echo 'index';
            } elseif ($method === 'POST') {
                echo 'create';
            }
        } else {
            switch ($method) {
                case 'GET':
                    echo "show $id";
                    break;
                case 'PATCH':
                    echo "update $id";
                    break;
                case "DELETE":
                    echo "delete $id";
                    break;
            }
        }
    }
}
