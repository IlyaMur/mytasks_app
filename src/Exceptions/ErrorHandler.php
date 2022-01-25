<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Exceptions;

use Throwable;
use ErrorException;

/**
 * Error and exception handler
 *
 * PHP version 8.0
 */
class ErrorHandler
{
    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $errno Error level
     * @param string $errstr Error message
     * @param string $errfile Filename the error was raised in
     * @param int $errline Line number in the file
     *
     * @return void
     */
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

    /**
     * Exception handler.
     * Selecting an exception output. Log or render to the screen.
     *
     * @param Throwable $exception The exception
     *
     * @return void
     */
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);

        // SHOW_ERROS const configuring in the configuration file
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
                'message' => "Something went wrong, for additional info contact app admin",
            ]);
        }
    }
}
