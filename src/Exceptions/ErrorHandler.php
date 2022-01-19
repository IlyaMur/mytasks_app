<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Exceptions;

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

        if (SHOW_ERRORS) {
            echo json_encode([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        } else {
            $logMessage = "Fatal error\n";
            $logMessage .= "Message: '" . $exception->getMessage() . "'\n";
            $logMessage .= "Stack trace: " . $exception->getTraceAsString() . "\n";
            $logMessage .= "Thrown in '" . $exception->getFile() . "\n";
            $logMessage .= "Line: " . $exception->getLine();

            error_log($logMessage);

            echo json_encode([
                'message' => "Something went wrong, for additional info contact app admin ",
            ]);
        }
    }
}
