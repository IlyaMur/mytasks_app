<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode('/', $path);

$resource = $parts[2];
$id = $parts[3] ?? null;

if ($resource !== 'tasks') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/src/TasksController.php';

$controller = new TasksApp\TaskController();

$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
