<?php

declare(strict_types=1);

namespace TasksApp;

use TasksApp\Database;
use PDO;

class TaskGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAllForUser(int $userId): array
    {
        $sql = "SELECT *
                FROM task 
                WHERE user_id = :userId
                ORDER BY name";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_completed'] = (bool) $row['is_completed'];
            $data[] = $row;
        }

        return $data;
    }

    public function getForUser(string $id, int $userId): array | false
    {
        $sql = "SELECT *
                FROM task 
                WHERE id = :id
                AND user_id = :userId";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->bindParam('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // for returning bool, not int 1/0 
        if ($data !== false) {
            $data['is_completed'] = (bool) $data['is_completed'];
        }

        return $data;
    }

    public function createForUser(array $data, int $userId): string | false
    {
        $sql = "INSERT INTO task (name, priority, is_completed, user_id) 
                VALUES (:name, :priority, :is_completed, :user_id)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('is_completed', $data['is_completed'] ?? false, PDO::PARAM_BOOL);

        if (empty($data['priority'])) {
            $stmt->bindValue('priority', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue('priority', $data['priority'], PDO::PARAM_INT);
        }

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteForUser(string $id, int $userId): int
    {
        $sql = 'DELETE FROM task
                WHERE id = :id
                AND user_id = :userId';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function updateForUser(string $id, array $data, int $userId): int
    {
        $fields = [];

        if (array_key_exists('name', $data)) {
            $fields['name'] = [
                $data['name'],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists('priority', $data)) {
            $fields['priority'] = [
                $data['priority'],
                is_null($data['priority']) ? PDO::PARAM_NULL : PDO::PARAM_INT
            ];
        }

        if (array_key_exists('is_completed', $data)) {
            $fields['is_completed'] = [
                $data['is_completed'],
                PDO::PARAM_BOOL
            ];
        }

        // if input is empty
        if (empty($fields)) {
            return 0;
        }

        $sets = array_map(
            fn ($val) => "$val = :$val",
            array_keys($fields)
        );

        $sql = "UPDATE task"
            . " SET " . implode(", ", $sets)
            . " WHERE id = :id"
            . " AND user_id = :userId";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);

        foreach ($fields as $name => $val) {
            $stmt->bindValue($name, $val[0], $val[1]);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }
}
