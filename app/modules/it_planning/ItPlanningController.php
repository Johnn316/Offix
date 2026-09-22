<?php

namespace App\Modules\ItPlanning;

use App\Core\Controller;
use App\Models\Contact;

class ItPlanningController extends Controller
{
    private ItAsset $model;

    private array $assetTypes = ['laptop','desktop','server','monitor','phone','tablet','printer','network','other'];
    private array $statuses   = ['active','inactive','maintenance','retired'];

    public function __construct()
    {
        $this->model = new ItAsset();
    }

    public function index(): void
    {
        $this->render('it_planning.index', [
            'pageTitle' => 'IT Planning',
            'grouped'   => $this->model->groupedByType(),
            'expiring'  => $this->model->warrantyExpiringSoon(60),
        ]);
    }

    public function create(): void
    {
        $this->render('it_planning.create', [
            'pageTitle'  => 'Add Asset',
            'contacts'   => (new Contact())->all(),
            'assetTypes' => $this->assetTypes,
            'statuses'   => $this->statuses,
            'errors'     => [],
            'old'        => [],
        ]);
    }

    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('it_planning.create', [
                'pageTitle'  => 'Add Asset',
                'contacts'   => (new Contact())->all(),
                'assetTypes' => $this->assetTypes,
                'statuses'   => $this->statuses,
                'errors'     => $errors,
                'old'        => $data,
            ]);
            return;
        }

        $id = $this->model->create($data);
        $_SESSION['flash_success'] = 'Asset "' . $data['name'] . '" added.';
        $this->redirect('/it_planning/' . $id);
    }

    public function show(string $id): void
    {
        $asset = $this->model->find((int) $id);
        if (!$asset) $this->abort(404, 'Asset not found');

        $this->render('it_planning.show', [
            'pageTitle' => $asset['name'],
            'asset'     => $asset,
        ]);
    }

    public function edit(string $id): void
    {
        $asset = $this->model->find((int) $id);
        if (!$asset) $this->abort(404, 'Asset not found');

        $this->render('it_planning.edit', [
            'pageTitle'  => 'Edit — ' . $asset['name'],
            'asset'      => $asset,
            'contacts'   => (new Contact())->all(),
            'assetTypes' => $this->assetTypes,
            'statuses'   => $this->statuses,
            'errors'     => [],
        ]);
    }

    public function update(string $id): void
    {
        $asset = $this->model->find((int) $id);
        if (!$asset) $this->abort(404, 'Asset not found');

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('it_planning.edit', [
                'pageTitle'  => 'Edit — ' . $asset['name'],
                'asset'      => array_merge($asset, $data),
                'contacts'   => (new Contact())->all(),
                'assetTypes' => $this->assetTypes,
                'statuses'   => $this->statuses,
                'errors'     => $errors,
            ]);
            return;
        }

        $this->model->update((int) $id, $data);
        $_SESSION['flash_success'] = 'Asset updated.';
        $this->redirect('/it_planning/' . $id);
    }

    public function delete(string $id): void
    {
        $asset = $this->model->find((int) $id);
        if (!$asset) $this->abort(404, 'Asset not found');

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'Asset deleted.';
        $this->redirect('/it_planning');
    }

    private function sanitize(array $post): array
    {
        return [
            'name'            => trim($post['name']            ?? ''),
            'asset_type'      => trim($post['asset_type']      ?? 'other'),
            'serial_number'   => trim($post['serial_number']   ?? ''),
            'status'          => trim($post['status']          ?? 'active'),
            'purchase_date'   => trim($post['purchase_date']   ?? ''),
            'warranty_expiry' => trim($post['warranty_expiry'] ?? ''),
            'assigned_to'     => trim($post['assigned_to']     ?? ''),
            'location'        => trim($post['location']        ?? ''),
            'notes'           => trim($post['notes']           ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name']))       $errors['name']       = 'Asset name is required.';
        if (empty($data['asset_type'])) $errors['asset_type'] = 'Asset type is required.';
        return $errors;
    }
}
