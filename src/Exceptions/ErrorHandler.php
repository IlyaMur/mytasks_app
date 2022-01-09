<?php

declare(strict_types=1);

namespace TasksApp\Exceptions;

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
            $logMessage = '';
            $log = dirname(__DIR__) . '/../logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $logMessage = "<h1>Fatal error</h1>";
            $logMessage .= "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            $logMessage .= "<p>Message: '" . $exception->getMessage() . "'</p>";
            $logMessage .= "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            $logMessage .= "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";

            error_log($logMessage);

            echo json_encode([
                'message' => "Something went wrong, for additional info contact app admin ",
            ]);
        }
    }
}
