<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Gateways;

use Ilyamur\TasksApp\Services\Database;
use PDO;

class UserGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

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
