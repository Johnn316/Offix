<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;
use App\Models\User;

class UserController extends Controller
{
    private User  $model;
    private array $roles     = ['admin', 'manager', 'user'];
    private array $languages = ['en-US' => 'English', 'de-CH' => 'Schweizerdeutsch'];

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(): void
    {
        $this->render('users.index', [
            'pageTitle' => __('users.title'),
            'users'     => $this->model->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('users.create', [
            'pageTitle' => __('users.new'),
            'roles'     => $this->roles,
            'languages' => $this->languages,
            'errors'    => [],
            'old'       => [],
        ]);
    }

    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('users.create', [
                'pageTitle' => __('users.new'),
                'roles'     => $this->roles,
                'languages' => $this->languages,
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        $this->model->create($data);
        $_SESSION['flash_success'] = 'User created.';
        $this->redirect('/users');
    }

    public function show(string $id): void
    {
        $user = $this->model->find((int) $id);
        if (!$user) $this->abort(404, 'User not found');

        $this->render('users.show', [
            'pageTitle' => $user['name'],
            'user'      => $user,
        ]);
    }

    public function edit(string $id): void
    {
        $user = $this->model->find((int) $id);
        if (!$user) $this->abort(404, 'User not found');

        $this->render('users.edit', [
            'pageTitle' => __('users.edit') . ' — ' . $user['name'],
            'user'      => $user,
            'roles'     => $this->roles,
            'languages' => $this->languages,
            'errors'    => [],
        ]);
    }

    public function update(string $id): void
    {
        $user = $this->model->find((int) $id);
        if (!$user) $this->abort(404, 'User not found');

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, (int) $id);

        if (!empty($errors)) {
            $this->render('users.edit', [
                'pageTitle' => __('users.edit') . ' — ' . $user['name'],
                'user'      => array_merge($user, $data),
                'roles'     => $this->roles,
                'languages' => $this->languages,
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update((int) $id, $data);

        if (!empty($data['password'])) {
            $this->model->updatePassword((int) $id, $data['password']);
        }

        $_SESSION['flash_success'] = 'User updated.';
        $this->redirect('/users');
    }

    public function delete(string $id): void
    {
        if ((int) $id === (int) $_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'You cannot delete your own account.';
            $this->redirect('/users');
        }

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'User deleted.';
        $this->redirect('/users');
    }

    private function sanitize(array $post): array
    {
        return [
            'name'      => trim($post['name']      ?? ''),
            'email'     => trim($post['email']     ?? ''),
            'password'  => trim($post['password']  ?? ''),
            'role'      => trim($post['role']       ?? 'user'),
            'language'  => trim($post['language']  ?? 'en-US'),
            'is_active' => isset($post['is_active']) ? 1 : 0,
        ];
    }

    private function validate(array $data, int $excludeId = 0): array
    {
        $errors = [];
        if (empty($data['name']))  $errors['name']  = 'Name is required.';
        if (empty($data['email'])) $errors['email'] = 'Email is required.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';
        elseif ($this->model->emailExists($data['email'], $excludeId)) $errors['email'] = 'Email already in use.';
        if ($excludeId === 0 && empty($data['password'])) $errors['password'] = 'Password is required for new users.';
        if (!empty($data['password']) && strlen($data['password']) < 8) $errors['password'] = 'Password must be at least 8 characters.';
        if (!in_array($data['role'], ['admin', 'manager', 'user'], true)) $errors['role'] = 'Invalid role.';
        return $errors;
    }
}
