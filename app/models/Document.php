<?php

namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    public function all(): array
    {
        return $this->query(
            "SELECT d.*, u.name AS owner_name
             FROM documents d
             JOIN users u ON u.id = d.owner_id
             ORDER BY d.updated_at DESC"
        );
    }

    public function find(string $id): ?array
    {
        return $this->queryOne(
            "SELECT d.*, u.name AS owner_name
             FROM documents d
             JOIN users u ON u.id = d.owner_id
             WHERE d.id = ?",
            [$id]
        );
    }

    public function create(int $ownerId, string $title = 'Untitled Document'): string
    {
        $id = bin2hex(random_bytes(16));
        $this->execute(
            "INSERT INTO documents (id, owner_id, title) VALUES (?, ?, ?)",
            [$id, $ownerId, $title]
        );
        return $id;
    }

    public function updateTitle(string $id, string $title): void
    {
        $this->execute("UPDATE documents SET title = ? WHERE id = ?", [$title, $id]);
    }

    public function saveContent(string $id, string $content, int $editorId = 0): void
    {
        if ($editorId > 0) {
            $this->execute(
                "UPDATE documents SET content = ?, last_editor_id = ?, updated_at = NOW() WHERE id = ?",
                [$content, $editorId, $id]
            );
        } else {
            $this->execute(
                "UPDATE documents SET content = ?, updated_at = NOW() WHERE id = ?",
                [$content, $id]
            );
        }
    }

    public function getState(string $id): ?array
    {
        return $this->queryOne(
            "SELECT content, last_editor_id, UNIX_TIMESTAMP(updated_at) AS ts FROM documents WHERE id = ?",
            [$id]
        );
    }

    public function activeEditors(string $docId): array
    {
        $rows = $this->query(
            "SELECT sender_id, payload FROM signaling_messages
             WHERE room_id = ? AND type = 'heartbeat'
               AND created_at > DATE_SUB(NOW(), INTERVAL 15 SECOND)
             GROUP BY sender_id
             ORDER BY id DESC",
            [$docId]
        );

        $result = [];
        foreach ($rows as $row) {
            $p = is_string($row['payload'])
                ? (json_decode($row['payload'], true) ?? [])
                : (array) $row['payload'];
            $result[] = [
                'id'   => $row['sender_id'],
                'name' => $p['userName'] ?? 'User',
            ];
        }
        return $result;
    }

    public function delete(string $id): void
    {
        $this->execute("DELETE FROM documents WHERE id = ?", [$id]);
    }

    // ── Versions ──────────────────────────────────────────────────────────────

    public function versions(string $docId): array
    {
        return $this->query(
            "SELECT dv.id, dv.document_id, dv.created_at, u.name AS created_by_name
             FROM document_versions dv
             JOIN users u ON u.id = dv.created_by
             WHERE dv.document_id = ?
             ORDER BY dv.created_at DESC
             LIMIT 100",
            [$docId]
        );
    }

    public function findVersion(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM document_versions WHERE id = ?",
            [$id]
        );
    }

    public function saveVersion(string $docId, string $content, int $userId): void
    {
        $this->execute(
            "INSERT INTO document_versions (document_id, content, created_by) VALUES (?, ?, ?)",
            [$docId, $content, $userId]
        );
    }

    // ── Signaling ─────────────────────────────────────────────────────────────

    public function sendSignal(string $roomId, int $senderId, ?int $receiverId, string $type, array $payload): void
    {
        $this->execute(
            "INSERT INTO signaling_messages (room_id, sender_id, receiver_id, type, payload)
             VALUES (?, ?, ?, ?, ?)",
            [$roomId, $senderId, $receiverId, $type, json_encode($payload)]
        );
    }

    public function pollSignals(string $roomId, int $userId, int $afterId): array
    {
        $rows = $this->query(
            "SELECT * FROM signaling_messages
             WHERE room_id = ?
               AND id > ?
               AND sender_id != ?
               AND (receiver_id IS NULL OR receiver_id = ?)
             ORDER BY id ASC
             LIMIT 50",
            [$roomId, $afterId, $userId, $userId]
        );
        // Decode JSON payload so the response is ready for the browser
        foreach ($rows as &$row) {
            $row['payload'] = json_decode($row['payload'], true) ?? [];
        }
        return $rows;
    }

    public function cleanOldSignals(): void
    {
        $this->execute(
            "DELETE FROM signaling_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
    }
}
