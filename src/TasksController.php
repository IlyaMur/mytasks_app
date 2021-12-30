<?php

namespace TasksApp;

class TaskController
{
    public function processRequest(string $method, ?int $id)
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
