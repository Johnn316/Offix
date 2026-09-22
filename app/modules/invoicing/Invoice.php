<?php

namespace App\Modules\Invoicing;

use App\Core\Model;

class Invoice extends Model
{
    public function all(): array
    {
        return $this->query(
            "SELECT i.*, c.name AS contact_name, p.name AS project_name
             FROM   invoices i
             LEFT JOIN contacts c ON c.id = i.contact_id
             LEFT JOIN projects p ON p.id = i.project_id
             ORDER BY i.issue_date DESC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->queryOne(
            "SELECT i.*, c.name AS contact_name, p.name AS project_name
             FROM   invoices i
             LEFT JOIN contacts c ON c.id = i.contact_id
             LEFT JOIN projects p ON p.id = i.project_id
             WHERE  i.id = ?",
            [$id]
        );
    }

    public function items(int $invoiceId): array
    {
        return $this->query(
            'SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order',
            [$invoiceId]
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO invoices (number, contact_id, project_id, issue_date, due_date, status, notes, tax_rate)
             VALUES (:number, :contact_id, :project_id, :issue_date, :due_date, :status, :notes, :tax_rate)",
            [
                ':number'     => $data['number'],
                ':contact_id' => $data['contact_id'] ?: null,
                ':project_id' => $data['project_id'] ?: null,
                ':issue_date' => $data['issue_date'],
                ':due_date'   => $data['due_date']   ?: null,
                ':status'     => $data['status'],
                ':notes'      => $data['notes']      ?: null,
                ':tax_rate'   => (float) ($data['tax_rate'] ?? 0),
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute(
            "UPDATE invoices SET number=:number, contact_id=:contact_id, project_id=:project_id,
             issue_date=:issue_date, due_date=:due_date, status=:status, notes=:notes, tax_rate=:tax_rate
             WHERE id=:id",
            [
                ':number'     => $data['number'],
                ':contact_id' => $data['contact_id'] ?: null,
                ':project_id' => $data['project_id'] ?: null,
                ':issue_date' => $data['issue_date'],
                ':due_date'   => $data['due_date']   ?: null,
                ':status'     => $data['status'],
                ':notes'      => $data['notes']      ?: null,
                ':tax_rate'   => (float) ($data['tax_rate'] ?? 0),
                ':id'         => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM invoices WHERE id = ?', [$id]);
    }

    public function saveItems(int $invoiceId, array $items): void
    {
        $this->execute('DELETE FROM invoice_items WHERE invoice_id = ?', [$invoiceId]);
        foreach ($items as $i => $item) {
            $qty    = (float) ($item['quantity']   ?? 1);
            $price  = (float) ($item['unit_price'] ?? 0);
            $amount = round($qty * $price, 2);
            $this->execute(
                "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$invoiceId, $item['description'], $qty, $price, $amount, $i]
            );
        }
    }

    /** Generate next invoice number: INV-YYYY-NNNN */
    public function generateNumber(): string
    {
        $year  = date('Y');
        $count = (int) $this->queryOne(
            'SELECT COUNT(*) as c FROM invoices WHERE YEAR(created_at) = ?', [$year]
        )['c'];
        return 'INV-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function totals(array $items, float $taxRate): array
    {
        $subtotal = array_sum(array_column($items, 'amount'));
        $tax      = round($subtotal * ($taxRate / 100), 2);
        return [
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => round($subtotal + $tax, 2),
        ];
    }
}
