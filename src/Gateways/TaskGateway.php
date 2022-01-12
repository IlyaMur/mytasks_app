<?php

declare(strict_types=1);

namespace TasksApp\Gateways;

use TasksApp\Core\Database;
use PDO;

class TaskGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAllForUser(string $userId): array
    {
        $sql = "SELECT *
                FROM task 
                WHERE user_id = :userId
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['completed'] = (bool) $row['completed'];
            $data[] = $row;
        }

        return $data;
    }

    public function getForUser(string $id, string $userId): array | false
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
            $data['completed'] = (bool) $data['completed'];
        }

        return $data;
    }

    public function createForUser(array $data, string $userId): string | false
    {
        $sql = "INSERT INTO task (title, body, priority, completed, user_id) 
                VALUES (:title, :body, :priority, :completed, :user_id)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue('body', $data['body'], PDO::PARAM_STR);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('completed', $data['completed'] ?? false, PDO::PARAM_BOOL);

        if (empty($data['priority'])) {
            $stmt->bindValue('priority', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue('priority', $data['priority'], PDO::PARAM_INT);
        }

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function deleteForUser(string $id, string $userId): int
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

    public function updateForUser(string $id, array $data, string $userId): int
    {
        $fields = [];

        if (array_key_exists('title', $data)) {
            $fields['title'] = [
                $data['title'],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists('body', $data)) {
            $fields['body'] = [
                $data['body'],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists('priority', $data)) {
            $fields['priority'] = [
                $data['priority'],
                is_null($data['priority']) ? PDO::PARAM_NULL : PDO::PARAM_INT
            ];
        }

        if (array_key_exists('completed', $data)) {
            $fields['completed'] = [
                $data['completed'],
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
