<?php

set_error_handler('TasksApp\ErrorHandler::handleError');
set_exception_handler('TasksApp\ErrorHandler::handleException');

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
