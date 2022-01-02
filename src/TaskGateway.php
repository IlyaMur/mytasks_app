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

    public function getAll(): array
    {
        $sql = "SELECT *
                FROM task 
                ORDER BY name";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_completed'] = (bool) $row['is_completed'];
            $data[] = $row;
        }

        return $data;
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM task 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // for returning bool, not int 1/0 
        if ($data !== false) {
            $data['is_completed'] = (bool) $data['is_completed'];
        }

        return $data;
    }

    public function create(array $data): string | false
    {
        $sql = "INSERT INTO task (name, priority, is_completed) 
                VALUES (:name, :priority, :is_completed)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue('is_completed', $data['is_completed'] ?? false, PDO::PARAM_BOOL);

        if (empty($data['priority'])) {
            $stmt->bindValue('priority', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue('priority', $data['priority'], PDO::PARAM_INT);
        }

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int
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
            . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);

        foreach ($fields as $name => $val) {
            $stmt->bindValue($name, $val[0], $val[1]);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }
}
