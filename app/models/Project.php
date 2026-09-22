<?php

namespace App\Models;

use App\Core\Model;

class Project extends Model
{
    private string $table = 'projects';

    /** All projects ordered by start date (most recent first). */
    public function all(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY start_date DESC, created_at DESC"
        );
    }

    /** Single project by PK. */
    public function find(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /** Insert a new project; return the new ID. */
    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table}
                (name, description, status, start_date, end_date)
             VALUES
                (:name, :description, :status, :start_date, :end_date)",
            [
                ':name'        => $data['name'],
                ':description' => $data['description'] ?: null,
                ':status'      => $data['status'],
                ':start_date'  => $data['start_date']  ?: null,
                ':end_date'    => $data['end_date']    ?: null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /** Update an existing project. */
    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET name = :name, description = :description, status = :status,
                 start_date = :start_date, end_date = :end_date
             WHERE id = :id",
            [
                ':name'        => $data['name'],
                ':description' => $data['description'] ?: null,
                ':status'      => $data['status'],
                ':start_date'  => $data['start_date']  ?: null,
                ':end_date'    => $data['end_date']    ?: null,
                ':id'          => $id,
            ]
        );
    }

    /** Delete project (FK on tasks is SET NULL, so tasks remain). */
    public function delete(int $id): void
    {
        $this->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    /** All tasks belonging to this project (with contact name). */
    public function tasks(int $projectId): array
    {
        return $this->query(
            "SELECT t.*, c.name AS contact_name
             FROM   tasks t
             LEFT JOIN contacts c ON c.id = t.contact_id
             WHERE  t.project_id = ?
             ORDER BY t.due_date ASC",
            [$projectId]
        );
    }
}
