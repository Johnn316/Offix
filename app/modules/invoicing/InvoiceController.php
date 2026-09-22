<?php

namespace App\Modules\Invoicing;

use App\Core\Controller;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Setting;

class InvoiceController extends Controller
{
    private Invoice $model;
    private Setting $settings;
    private array $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    public function __construct()
    {
        $this->model    = new Invoice();
        $this->settings = new Setting();
    }

    public function index(): void
    {
        $this->render('invoicing.index', [
            'pageTitle'  => __('invoicing.title'),
            'invoices'   => $this->model->all(),
            'currencies' => $this->settings->currencies(),
        ]);
    }

    public function create(): void
    {
        $currencies = $this->settings->currencies();
        $this->render('invoicing.create', [
            'pageTitle'       => __('invoicing.new'),
            'contacts'        => (new Contact())->all(),
            'projects'        => (new Project())->all(),
            'statuses'        => $this->statuses,
            'currencies'      => $currencies,
            'defaultCurrency' => $this->settings->defaultCurrency(),
            'number'          => $this->model->generateNumber(),
            'errors'          => [],
            'old'             => [],
            'oldItems'        => [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
        ]);
    }

    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $items  = $this->sanitizeItems($_POST['items'] ?? []);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $currencies = $this->settings->currencies();
            $this->render('invoicing.create', [
                'pageTitle'       => __('invoicing.new'),
                'contacts'        => (new Contact())->all(),
                'projects'        => (new Project())->all(),
                'statuses'        => $this->statuses,
                'currencies'      => $currencies,
                'defaultCurrency' => $this->settings->defaultCurrency(),
                'number'          => $data['number'],
                'errors'          => $errors,
                'old'             => $data,
                'oldItems'        => $items,
            ]);
            return;
        }

        $id = $this->model->create($data);
        $this->model->saveItems($id, $items);
        $_SESSION['flash_success'] = 'Invoice ' . $data['number'] . ' created.';
        $this->redirect('/invoicing/' . $id);
    }

    public function show(string $id): void
    {
        $invoice = $this->model->find((int) $id);
        if (!$invoice) $this->abort(404, 'Invoice not found');

        $items  = $this->model->items((int) $id);
        $totals = $this->model->totals($items, (float) $invoice['tax_rate']);

        $this->render('invoicing.show', [
            'pageTitle' => $invoice['number'],
            'invoice'   => $invoice,
            'items'     => $items,
            'totals'    => $totals,
        ]);
    }

    public function edit(string $id): void
    {
        $invoice = $this->model->find((int) $id);
        if (!$invoice) $this->abort(404, 'Invoice not found');

        $items = $this->model->items((int) $id);
        if (empty($items)) {
            $items = [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'amount' => 0]];
        }

        $this->render('invoicing.edit', [
            'pageTitle'  => __('invoicing.edit') . ' — ' . $invoice['number'],
            'invoice'    => $invoice,
            'items'      => $items,
            'contacts'   => (new Contact())->all(),
            'projects'   => (new Project())->all(),
            'statuses'   => $this->statuses,
            'currencies' => $this->settings->currencies(),
            'errors'     => [],
        ]);
    }

    public function update(string $id): void
    {
        $invoice = $this->model->find((int) $id);
        if (!$invoice) $this->abort(404, 'Invoice not found');

        $data   = $this->sanitize($_POST);
        $items  = $this->sanitizeItems($_POST['items'] ?? []);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('invoicing.edit', [
                'pageTitle'  => __('invoicing.edit') . ' — ' . $invoice['number'],
                'invoice'    => array_merge($invoice, $data),
                'items'      => $items,
                'contacts'   => (new Contact())->all(),
                'projects'   => (new Project())->all(),
                'statuses'   => $this->statuses,
                'currencies' => $this->settings->currencies(),
                'errors'     => $errors,
            ]);
            return;
        }

        $this->model->update((int) $id, $data);
        $this->model->saveItems((int) $id, $items);
        $_SESSION['flash_success'] = 'Invoice updated.';
        $this->redirect('/invoicing/' . $id);
    }

    public function delete(string $id): void
    {
        $invoice = $this->model->find((int) $id);
        if (!$invoice) $this->abort(404, 'Invoice not found');

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'Invoice deleted.';
        $this->redirect('/invoicing');
    }

    public function printView(string $id): void
    {
        $invoice = $this->model->find((int) $id);
        if (!$invoice) $this->abort(404, 'Invoice not found');

        $items  = $this->model->items((int) $id);
        $totals = $this->model->totals($items, (float) $invoice['tax_rate']);

        $this->render('invoicing.print', [
            'pageTitle' => 'Print — ' . $invoice['number'],
            'invoice'   => $invoice,
            'items'     => $items,
            'totals'    => $totals,
        ], 'print_layout');
    }

    private function sanitize(array $post): array
    {
        return [
            'number'     => trim($post['number']     ?? ''),
            'contact_id' => trim($post['contact_id'] ?? ''),
            'project_id' => trim($post['project_id'] ?? ''),
            'issue_date' => trim($post['issue_date'] ?? ''),
            'due_date'   => trim($post['due_date']   ?? ''),
            'status'     => trim($post['status']     ?? 'draft'),
            'currency'   => trim($post['currency']   ?? 'USD'),
            'notes'      => trim($post['notes']      ?? ''),
            'tax_rate'   => (float) ($post['tax_rate'] ?? 0),
        ];
    }

    private function sanitizeItems(array $rawItems): array
    {
        $items = [];
        foreach ($rawItems as $item) {
            if (empty(trim($item['description'] ?? ''))) continue;
            $qty   = max(0, (float) ($item['quantity']   ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $items[] = [
                'description' => trim($item['description']),
                'quantity'    => $qty,
                'unit_price'  => $price,
                'amount'      => round($qty * $price, 2),
            ];
        }
        return $items;
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['number']))     $errors['number']     = 'Invoice number is required.';
        if (empty($data['issue_date'])) $errors['issue_date'] = 'Issue date is required.';
        if (!in_array($data['status'], $this->statuses, true)) $errors['status'] = 'Invalid status.';
        return $errors;
    }
}
