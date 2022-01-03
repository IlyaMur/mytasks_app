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

    public function create(array $data): string | false
    {
        $sql = "INSERT INTO user (name, username, password_hash, api_key) 
                VALUES (:name, :username, :password_hash, :api_key)";

        $stmt = $this->conn->prepare($sql);

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(16));

        $stmt->bindValue('name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue('username', $data['username'], PDO::PARAM_STR);
        $stmt->bindValue('password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue('api_key', $apiKey, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $apiKey;
        } else {
            return false;
        }
    }

    public function getUser(string $username): array | false
    {
        $sql = 'SELECT * FROM user
                WHERE username = :username';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
