<?php

namespace App\Modules\ItPlanning;

use App\Core\Model;

class ItAsset extends Model
{
    public function all(): array
    {
        return $this->query(
            "SELECT a.*, c.name AS assigned_to_name
             FROM   it_assets a
             LEFT JOIN contacts c ON c.id = a.assigned_to
             ORDER BY a.asset_type, a.name"
        );
    }

    public function find(int $id): ?array
    {
        return $this->queryOne(
            "SELECT a.*, c.name AS assigned_to_name
             FROM   it_assets a
             LEFT JOIN contacts c ON c.id = a.assigned_to
             WHERE  a.id = ?",
            [$id]
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO it_assets (name, asset_type, serial_number, status, purchase_date, warranty_expiry, assigned_to, location, notes)
             VALUES (:name, :asset_type, :serial_number, :status, :purchase_date, :warranty_expiry, :assigned_to, :location, :notes)",
            [
                ':name'             => $data['name'],
                ':asset_type'       => $data['asset_type'],
                ':serial_number'    => $data['serial_number']    ?: null,
                ':status'           => $data['status'],
                ':purchase_date'    => $data['purchase_date']    ?: null,
                ':warranty_expiry'  => $data['warranty_expiry']  ?: null,
                ':assigned_to'      => $data['assigned_to']      ?: null,
                ':location'         => $data['location']         ?: null,
                ':notes'            => $data['notes']            ?: null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE it_assets SET name=:name, asset_type=:asset_type, serial_number=:serial_number,
             status=:status, purchase_date=:purchase_date, warranty_expiry=:warranty_expiry,
             assigned_to=:assigned_to, location=:location, notes=:notes
             WHERE id=:id",
            [
                ':name'            => $data['name'],
                ':asset_type'      => $data['asset_type'],
                ':serial_number'   => $data['serial_number']   ?: null,
                ':status'          => $data['status'],
                ':purchase_date'   => $data['purchase_date']   ?: null,
                ':warranty_expiry' => $data['warranty_expiry'] ?: null,
                ':assigned_to'     => $data['assigned_to']     ?: null,
                ':location'        => $data['location']        ?: null,
                ':notes'           => $data['notes']           ?: null,
                ':id'              => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM it_assets WHERE id = ?', [$id]);
    }

    public function groupedByType(): array
    {
        $assets = $this->all();
        $grouped = [];
        foreach ($assets as $asset) {
            $grouped[$asset['asset_type']][] = $asset;
        }
        return $grouped;
    }

    public function warrantyExpiringSoon(int $days = 30): array
    {
        return $this->query(
            "SELECT * FROM it_assets
             WHERE warranty_expiry IS NOT NULL
               AND warranty_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY warranty_expiry",
            [$days]
        );
    }
}
