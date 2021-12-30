<?php

declare(strict_types=1);

namespace TasksApp;

use Throwable;

class ErrorHandler
{
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        echo json_encode([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }
}
