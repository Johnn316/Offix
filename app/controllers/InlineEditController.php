<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class InlineEditController extends Controller
{
    private array $schema = [
        'contact' => [
            'table'  => 'contacts',
            'fields' => ['name', 'email', 'phone', 'company', 'notes',
                         'address1', 'address2', 'city', 'state', 'zip', 'country'],
        ],
        'task' => [
            'table'  => 'tasks',
            'fields' => ['title', 'due_date', 'description'],
        ],
        'project' => [
            'table'  => 'projects',
            'fields' => ['name', 'start_date', 'end_date', 'description'],
        ],
    ];

    public function update(): void
    {
        header('Content-Type: application/json');

        $body  = json_decode(file_get_contents('php://input'), true);
        $model = trim($body['model'] ?? '');
        $id    = (int) ($body['id']    ?? 0);
        $field = trim($body['field']   ?? '');
        $value = $body['value'] ?? '';

        if (!isset($this->schema[$model])
            || $id <= 0
            || !in_array($field, $this->schema[$model]['fields'], true)
        ) {
            echo json_encode(['ok' => false, 'error' => 'Invalid request']);
            return;
        }

        $table = $this->schema[$model]['table'];
        $db    = Database::getInstance();
        $stmt  = $db->prepare("UPDATE `{$table}` SET `{$field}` = ? WHERE id = ?");
        $stmt->execute([$value === '' ? null : $value, $id]);

        echo json_encode(['ok' => true]);
    }
}
