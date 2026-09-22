<?php

namespace App\Models;

use App\Core\Database;

class Setting
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db->prepare('SELECT `value` FROM settings WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (string)$row['value'] : $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute([$key, $value]);
    }

    public function all(): array
    {
        $rows = $this->db->query('SELECT `key`, `value` FROM settings')->fetchAll(\PDO::FETCH_ASSOC);
        $out  = [];
        foreach ($rows as $row) $out[$row['key']] = $row['value'];
        return $out;
    }

    // Returns the enabled currency codes as an array, e.g. ['USD','CHF','PHP']
    public function currencies(): array
    {
        $raw = $this->get('currencies', 'USD');
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    public function defaultCurrency(): string
    {
        return $this->get('default_currency', 'USD');
    }
}
