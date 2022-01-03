<?php

declare(strict_types=1);

set_error_handler('TasksApp\ErrorHandler::handleError');
set_exception_handler('TasksApp\ErrorHandler::handleException');

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

header('Content-type: application/json; charset=UTF-8');
