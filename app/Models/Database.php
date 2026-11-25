<?php

namespace App\Models;

use mysqli;

final class Database
{
    private mysqli $connection;

    public function __construct()
    {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USERNAME,
                DB_PASSWORD,
                DB_DATABASE
            );

        } catch (\Throwable $exception) {
            throw new \RuntimeException($exception->getMessage());
        }

        if ($this->connection->connect_error) {
            throw new \RuntimeException('Não foi possível conectar ao banco: ' . $this->connection->connect_error);
        }
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function query(string $sql): array
    {
        $result = $this->connection->query($sql);

        if ($result === false) {
            throw new \RuntimeException($this->connection->error);
        }

        if ($result === true) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();

        return $rows;
    }

    public function execute(string $sql): bool
    {
        return $this->connection->query($sql);
    }

    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }
}
