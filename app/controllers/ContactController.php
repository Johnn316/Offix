<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;

class ContactController extends Controller
{
    private Contact $model;

    public function __construct()
    {
        $this->model = new Contact();
    }

    // ── List ───────────────────────────────────────────────────────────────
    public function index(): void
    {
        $contacts = $this->model->all();
        $this->render('contacts.index', [
            'pageTitle' => __('contacts.title'),
            'contacts'  => $contacts,
        ]);
    }

    // ── Create form ────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->render('contacts.create', [
            'pageTitle' => __('contacts.new'),
            'errors'    => [],
            'old'       => [],
        ]);
    }

    // ── Store (POST) ───────────────────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('contacts.create', [
                'pageTitle' => __('contacts.new'),
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        $id = $this->model->create($data);
        $_SESSION['flash_success'] = 'Contact created successfully.';
        $this->redirect('/contacts/' . $id);
    }

    // ── Show (detail) ──────────────────────────────────────────────────────
    public function show(string $id): void
    {
        $contact = $this->model->find((int) $id);
        if (!$contact) $this->abort(404, 'Contact not found');

        $tasks = $this->model->tasks((int) $id);

        $this->render('contacts.show', [
            'pageTitle' => $contact['name'],
            'contact'   => $contact,
            'tasks'     => $tasks,
        ]);
    }

    // ── Edit form ──────────────────────────────────────────────────────────
    public function edit(string $id): void
    {
        $contact = $this->model->find((int) $id);
        if (!$contact) $this->abort(404, 'Contact not found');

        $this->render('contacts.edit', [
            'pageTitle' => __('contacts.edit') . ' — ' . $contact['name'],
            'contact'   => $contact,
            'errors'    => [],
        ]);
    }

    // ── Update (POST) ──────────────────────────────────────────────────────
    public function update(string $id): void
    {
        $contact = $this->model->find((int) $id);
        if (!$contact) $this->abort(404, 'Contact not found');

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, (int) $id);

        if (!empty($errors)) {
            $this->render('contacts.edit', [
                'pageTitle' => __('contacts.edit') . ' — ' . $contact['name'],
                'contact'   => array_merge($contact, $data),
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update((int) $id, $data);
        $_SESSION['flash_success'] = 'Contact updated successfully.';
        $this->redirect('/contacts/' . $id);
    }

    // ── Delete (POST) ──────────────────────────────────────────────────────
    public function delete(string $id): void
    {
        $contact = $this->model->find((int) $id);
        if (!$contact) $this->abort(404, 'Contact not found');

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'Contact deleted.';
        $this->redirect('/contacts');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Strip whitespace; never let raw $_POST reach the model directly. */
    private function sanitize(array $post): array
    {
        return [
            'name'     => trim($post['name']     ?? ''),
            'email'    => trim($post['email']    ?? ''),
            'phone'    => trim($post['phone']    ?? ''),
            'company'  => trim($post['company']  ?? ''),
            'notes'    => trim($post['notes']    ?? ''),
            'address1' => trim($post['address1'] ?? ''),
            'address2' => trim($post['address2'] ?? ''),
            'city'     => trim($post['city']     ?? ''),
            'state'    => trim($post['state']    ?? ''),
            'zip'      => trim($post['zip']      ?? ''),
            'country'  => trim($post['country']  ?? ''),
        ];
    }

    /** Return an associative array of field → error messages. */
    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($data['name']) > 150) {
            $errors['name'] = 'Name must be 150 characters or fewer.';
        }

        if ($data['email'] !== '') {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }
        }

        if ($data['phone'] !== '' && mb_strlen($data['phone']) > 50) {
            $errors['phone'] = 'Phone must be 50 characters or fewer.';
        }

        return $errors;
    }
}
