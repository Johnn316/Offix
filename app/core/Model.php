<?php

namespace App\Core;

use PDO;

/**
 * Base Model — all models extend this.
 * Provides the PDO instance and common query helpers.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Run a SELECT and return all matching rows.
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Run a SELECT and return exactly one row (or null).
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Run an INSERT / UPDATE / DELETE and return affected row count.
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Return the last auto-increment ID after an INSERT.
     */
    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
