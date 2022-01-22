<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Gateways;

use Ilyamur\TasksApp\Services\Database;
use PDO;

/**
 * RefreshTokenGateway
 *
 * PHP version 8.0
 */
class RefreshTokenGateway
{
    /**
     * Database connection object
     *
     * @var PDO
     */
    private PDO $conn;

    /**
     * Secret key
     *
     * @var string
     */
    private string $key;

    /**
     * Class constructor
     *
     * @param Database $database Database object
     * @param string $key Secret key for encoding JWT
     *
     * @return void
     */
    public function __construct(Database $database, string $key)
    {
        $this->conn = $database->getConnection();
        $this->key = $key;
    }

    /**
     * Create new Refresh Token
     *
     * @param string $token Token
     * @param int $expiry Expiry time
     *
     * @return bool
     */
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

    /**
     * Delete old Refresh Token
     *
     * @param string $token Token
     *
     * @return int
     */
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

    /**
     * Find by tokens hash in the refresh_token table
     *
     * @param string $token Token
     *
     * @return mixed
     */
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

    /**
     * Hash token
     *
     * @param string $token Token
     *
     * @return string
     */
    public function getTokenHash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->key);
    }
}
