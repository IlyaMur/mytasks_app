<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Gateways;

use Ilyamur\TasksApp\Services\Database;
use PDO;

/**
 * UserGateway
 *
 * PHP version 8.0
 */
class UserGateway
{
    /**
     * Database connection object
     *
     * @var PDO
     */
    private PDO $conn;

    /**
     * Class constructor
     *
     * @param Database $database Database object
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    /**
     * Create new user
     *
     * @param array $userData user data
     *
     * @return mixed
     */
    public function create(array $userData): array | false
    {
        $sql = "INSERT INTO user (email, username, password_hash, api_key) 
                VALUES (:email, :username, :password_hash, :api_key)";

        $stmt = $this->conn->prepare($sql);

        $passwordHash = password_hash((string) $userData['password'], PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(16));

        $stmt->bindValue('email', $userData['email'], PDO::PARAM_STR);
        $stmt->bindValue('username', $userData['username'], PDO::PARAM_STR);
        $stmt->bindValue('password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue('api_key', $apiKey, PDO::PARAM_STR);

        return $stmt->execute() ? [$apiKey, $this->conn->lastInsertId()] : false;
    }

    /**
     * Get user by emai new user
     *
     * @param string $email Email
     *
     * @return mixed
     */
    public function getByEmail(string $email): array | false
    {
        $sql = 'SELECT * 
                FROM user
                WHERE email = :email';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by API key
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function getByAPIKey(string $key): array | false
    {
        $sql = 'SELECT * 
                FROM user
                WHERE api_key = :api_key';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('api_key', $key, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by id
     *
     * @param string $id Id
     *
     * @return mixed
     */
    public function getByID(string $id): array | false
    {
        $sql = "SELECT * 
                FROM user
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
