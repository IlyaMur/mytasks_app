<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Gateways;

use Ilyamur\TasksApp\Services\Database;
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
        $hash = $this->getTokenHash($token);

        $sql = "INSERT INTO refresh_token (token_hash, expires_at) 
                VALUES (:token_hash, :expires_at)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('token_hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue('expires_at', $expiry, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(string $token): int
    {
        $hash = $this->getTokenHash($token);

        $sql = 'DELETE FROM refresh_token
                WHERE token_hash = :token_hash';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('token_hash', $hash, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getByToken(string $token): array | false
    {
        $hash = $this->getTokenHash($token);

        $sql = "SELECT * 
                FROM refresh_token
                WHERE token_hash = :token_hash";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('token_hash', $hash, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTokenHash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->key);
    }
}
