<?php

namespace App\Models;

use App\Core\Model;
use DateTime;

class Task extends Model
{
    private string $table = 'tasks';

    /** All tasks with joined contact and project names. */
    public function all(): array
    {
        return $this->query(
            "SELECT t.*,
                    c.name AS contact_name,
                    p.name AS project_name
             FROM   {$this->table} t
             LEFT JOIN contacts c ON c.id = t.contact_id
             LEFT JOIN projects p ON p.id = t.project_id
             ORDER BY t.due_date ASC, t.created_at DESC"
        );
    }

    /** Upcoming tasks for the planner (pending + in_progress, ordered by due date). */
    public function upcoming(): array
    {
        return $this->query(
            "SELECT t.*,
                    c.name AS contact_name,
                    p.name AS project_name
             FROM   {$this->table} t
             LEFT JOIN contacts c ON c.id = t.contact_id
             LEFT JOIN projects p ON p.id = t.project_id
             WHERE  t.status IN ('pending','in_progress')
             ORDER BY t.due_date ASC, t.priority DESC"
        );
    }

    /** Single task with joins. */
    public function find(int $id): ?array
    {
        return $this->queryOne(
            "SELECT t.*,
                    c.name AS contact_name,
                    p.name AS project_name
             FROM   {$this->table} t
             LEFT JOIN contacts c ON c.id = t.contact_id
             LEFT JOIN projects p ON p.id = t.project_id
             WHERE  t.id = ?",
            [$id]
        );
    }

    /** Insert a new task. */
    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table}
                (title, description, due_date, status, priority, contact_id, project_id,
                 is_recurring, recurrence_type, recurrence_interval, recurrence_end_date, parent_task_id)
             VALUES
                (:title, :description, :due_date, :status, :priority, :contact_id, :project_id,
                 :is_recurring, :recurrence_type, :recurrence_interval, :recurrence_end_date, :parent_task_id)",
            [
                ':title'               => $data['title'],
                ':description'         => $data['description']         ?: null,
                ':due_date'            => $data['due_date']            ?: null,
                ':status'              => $data['status'],
                ':priority'            => $data['priority'],
                ':contact_id'          => $data['contact_id']          ?: null,
                ':project_id'          => $data['project_id']          ?: null,
                ':is_recurring'        => (int) ($data['is_recurring'] ?? 0),
                ':recurrence_type'     => $data['recurrence_type']     ?: null,
                ':recurrence_interval' => (int) ($data['recurrence_interval'] ?? 1),
                ':recurrence_end_date' => $data['recurrence_end_date'] ?: null,
                ':parent_task_id'      => $data['parent_task_id']      ?: null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /** Update an existing task. */
    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET title = :title, description = :description, due_date = :due_date,
                 status = :status, priority = :priority,
                 contact_id = :contact_id, project_id = :project_id,
                 is_recurring = :is_recurring, recurrence_type = :recurrence_type,
                 recurrence_interval = :recurrence_interval,
                 recurrence_end_date = :recurrence_end_date
             WHERE id = :id",
            [
                ':title'               => $data['title'],
                ':description'         => $data['description']         ?: null,
                ':due_date'            => $data['due_date']            ?: null,
                ':status'              => $data['status'],
                ':priority'            => $data['priority'],
                ':contact_id'          => $data['contact_id']          ?: null,
                ':project_id'          => $data['project_id']          ?: null,
                ':is_recurring'        => (int) ($data['is_recurring'] ?? 0),
                ':recurrence_type'     => $data['recurrence_type']     ?: null,
                ':recurrence_interval' => (int) ($data['recurrence_interval'] ?? 1),
                ':recurrence_end_date' => $data['recurrence_end_date'] ?: null,
                ':id'                  => $id,
            ]
        );
    }

    /** Delete a task. */
    public function delete(int $id): void
    {
        $this->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    /**
     * Calculate the next due date for a recurring task.
     * Returns null if the next date would exceed the end date.
     */
    public static function nextDueDate(string $dueDate, string $type, int $interval, ?string $endDate): ?string
    {
        $date = new DateTime($dueDate);

        match ($type) {
            'daily'   => $date->modify("+{$interval} day"),
            'weekly'  => $date->modify("+{$interval} week"),
            'monthly' => $date->modify("+{$interval} month"),
            'yearly'  => $date->modify("+{$interval} year"),
            default   => null,
        };

        $next = $date->format('Y-m-d');

        if ($endDate && $next > $endDate) {
            return null;
        }

        return $next;
    }
}
