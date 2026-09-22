<?php

namespace App\Models;

use App\Core\Model;

class Contact extends Model
{
    private string $table = 'contacts';

    public function all(): array
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY name ASC");
    }

    public function find(int $id): ?array
    {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table}
                (name, email, phone, company, notes, address1, address2, city, state, zip, country)
             VALUES
                (:name, :email, :phone, :company, :notes, :address1, :address2, :city, :state, :zip, :country)",
            [
                ':name'     => $data['name'],
                ':email'    => $data['email']    ?: null,
                ':phone'    => $data['phone']    ?: null,
                ':company'  => $data['company']  ?: null,
                ':notes'    => $data['notes']    ?: null,
                ':address1' => $data['address1'] ?: null,
                ':address2' => $data['address2'] ?: null,
                ':city'     => $data['city']     ?: null,
                ':state'    => $data['state']    ?: null,
                ':zip'      => $data['zip']      ?: null,
                ':country'  => $data['country']  ?: null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET name = :name, email = :email, phone = :phone, company = :company,
                 notes = :notes, address1 = :address1, address2 = :address2,
                 city = :city, state = :state, zip = :zip, country = :country
             WHERE id = :id",
            [
                ':name'     => $data['name'],
                ':email'    => $data['email']    ?: null,
                ':phone'    => $data['phone']    ?: null,
                ':company'  => $data['company']  ?: null,
                ':notes'    => $data['notes']    ?: null,
                ':address1' => $data['address1'] ?: null,
                ':address2' => $data['address2'] ?: null,
                ':city'     => $data['city']     ?: null,
                ':state'    => $data['state']    ?: null,
                ':zip'      => $data['zip']      ?: null,
                ':country'  => $data['country']  ?: null,
                ':id'       => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function tasks(int $contactId): array
    {
        return $this->query(
            "SELECT t.*, p.name AS project_name
             FROM   tasks t
             LEFT JOIN projects p ON p.id = t.project_id
             WHERE  t.contact_id = ?
             ORDER BY t.due_date ASC",
            [$contactId]
        );
    }
}
