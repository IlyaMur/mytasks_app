<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Services;

use PDO;

/**
 * Database
 *
 * PHP version 8.0
 */
class Database
{
    /**
     * Database connection object
     *
     * @var mixed PDO or null
     */
    private ?PDO $conn = null;

    /**
     * Class constructor
     *
     * @param string $host DB host
     * @param string $name DB name
     * @param string $user DB user
     * @param string $password DB password
     *
     * @return void
     */
    public function __construct(
        private string $host,
        private string $name,
        private string $user,
        private string $password
    ) {
    }

    /**
     * Get DB connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if (is_null($this->conn)) {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

            $this->conn = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false
                ]
            );
        }
        return $this->conn;
    }
}
