<?php

declare(strict_types=1);

namespace TasksApp\Gateways;

use TasksApp\Core\Database;
use PDO;

class RefreshTokenGateway
{
    private PDO $conn;
    private string $key;

    public function __construct(Database $database, string $key)
    {
        $this->conn = $database->getConnection();
        $this->key = $key;
    }

    public function create(string $token, int $expiry): bool
    {
        $hash = hash_hmac('sha256', $token, $this->key);

        $sql = "INSERT INTO refresh_token (token_hash, expires_at) 
                VALUES (:token_hash, :expires_at)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('token_hash', $token, PDO::PARAM_STR);
        $stmt->bindValue('expires_at', $expiry, PDO::PARAM_INT);

        return $stmt->execute();
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

    public function getByID(int $id): array | false
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
