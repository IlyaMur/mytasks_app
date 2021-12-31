<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use TasksApp\Database;
use TasksApp\TaskController;

require dirname(__DIR__) . '/vendor/autoload.php';

set_exception_handler('TasksApp\ErrorHandler::handleException');

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

$dbName = $_ENV['DB_NAME'] ?? getenv('DB_HOST');
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER');
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST');

$parts = explode(
    '/',
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$resource = $parts[2];
$id = $parts[3] ?? null;

if ($resource !== 'tasks') {
    http_response_code(404);
    exit;
}

header('Content-type: application/json; charset=UTF-8');

$database = new Database($dbHost, $dbName, $dbUser, $dbPass);
$database->getConnection();

$controller = new TaskController();
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
