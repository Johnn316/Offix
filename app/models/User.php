<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function all(): array
    {
        return $this->query('SELECT id, name, email, role, language, is_active, avatar, last_login, created_at FROM users ORDER BY name');
    }

    public function find(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password, role, language, is_active)
             VALUES (:name, :email, :password, :role, :language, :is_active)',
            [
                ':name'      => $data['name'],
                ':email'     => $data['email'],
                ':password'  => password_hash($data['password'], PASSWORD_BCRYPT),
                ':role'      => $data['role']      ?? 'user',
                ':language'  => $data['language']  ?? 'en-US',
                ':is_active' => $data['is_active'] ?? 1,
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            'UPDATE users SET name=:name, email=:email, role=:role, language=:language, is_active=:is_active WHERE id=:id',
            [
                ':name'      => $data['name'],
                ':email'     => $data['email'],
                ':role'      => $data['role'],
                ':language'  => $data['language'],
                ':is_active' => $data['is_active'] ?? 1,
                ':id'        => $id,
            ]
        );
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $this->execute(
            'UPDATE users SET password = ? WHERE id = ?',
            [password_hash($newPassword, PASSWORD_BCRYPT), $id]
        );
    }

    public function updateAvatar(int $id, string $filename): void
    {
        $this->execute('UPDATE users SET avatar = ? WHERE id = ?', [$filename, $id]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->execute('UPDATE users SET last_login = NOW() WHERE id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM users WHERE id = ?', [$id]);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $row = $this->queryOne(
            'SELECT id FROM users WHERE email = ? AND id != ?',
            [$email, $excludeId]
        );
        return $row !== null;
    }
}
