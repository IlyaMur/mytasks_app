<?php

declare(strict_types=1);

namespace TasksApp;

use TasksApp\Database;
use PDO;

class UserGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function create(array $userData): string | false
    {
        $sql = "INSERT INTO user (name, username, password_hash, api_key) 
                VALUES (:name, :username, :password_hash, :api_key)";

        $stmt = $this->conn->prepare($sql);

        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(16));

        $stmt->bindValue('name', $userData['name'], PDO::PARAM_STR);
        $stmt->bindValue('username', $userData['username'], PDO::PARAM_STR);
        $stmt->bindValue('password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue('api_key', $apiKey, PDO::PARAM_STR);

        return $stmt->execute() ? $apiKey : false;
    }

    public function getByUsername(string $username): array | false
    {
        $sql = 'SELECT * 
                FROM user
                WHERE username = :username';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('username', $username, PDO::PARAM_STR);
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
}
