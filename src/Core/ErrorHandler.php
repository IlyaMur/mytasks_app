<?php

declare(strict_types=1);

namespace TasksApp\Core;

use Throwable;
use ErrorException;

class ErrorHandler
{
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): void {
        throw new ErrorException(
            code: 0,
            message: $errstr,
            filename: $errfile,
            line: $errline,
            severity: $errno
        );
    }

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
