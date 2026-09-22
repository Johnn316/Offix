<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    private Project $model;
    private array   $statuses = ['active', 'on_hold', 'completed', 'cancelled'];

    public function __construct()
    {
        $this->model = new Project();
    }

    // ── List ───────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->render('projects.index', [
            'pageTitle' => __('projects.title'),
            'projects'  => $this->model->all(),
        ]);
    }

    // ── Create form ────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->render('projects.create', [
            'pageTitle' => __('projects.new'),
            'statuses'  => $this->statuses,
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
            $this->render('projects.create', [
                'pageTitle' => __('projects.new'),
                'statuses'  => $this->statuses,
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        $id = $this->model->create($data);
        $_SESSION['flash_success'] = 'Project created successfully.';
        $this->redirect('/projects/' . $id);
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(string $id): void
    {
        $project = $this->model->find((int) $id);
        if (!$project) $this->abort(404, 'Project not found');

        $tasks = $this->model->tasks((int) $id);

        $this->render('projects.show', [
            'pageTitle' => $project['name'],
            'project'   => $project,
            'tasks'     => $tasks,
        ]);
    }

    // ── Edit form ──────────────────────────────────────────────────────────
    public function edit(string $id): void
    {
        $project = $this->model->find((int) $id);
        if (!$project) $this->abort(404, 'Project not found');

        $this->render('projects.edit', [
            'pageTitle' => __('projects.edit') . ' — ' . $project['name'],
            'project'   => $project,
            'statuses'  => $this->statuses,
            'errors'    => [],
        ]);
    }

    // ── Update (POST) ──────────────────────────────────────────────────────
    public function update(string $id): void
    {
        $project = $this->model->find((int) $id);
        if (!$project) $this->abort(404, 'Project not found');

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('projects.edit', [
                'pageTitle' => __('projects.edit') . ' — ' . $project['name'],
                'project'   => array_merge($project, $data),
                'statuses'  => $this->statuses,
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update((int) $id, $data);
        $_SESSION['flash_success'] = 'Project updated successfully.';
        $this->redirect('/projects/' . $id);
    }

    // ── Delete (POST) ──────────────────────────────────────────────────────
    public function delete(string $id): void
    {
        $project = $this->model->find((int) $id);
        if (!$project) $this->abort(404, 'Project not found');

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'Project deleted.';
        $this->redirect('/projects');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function sanitize(array $post): array
    {
        return [
            'name'        => trim($post['name']        ?? ''),
            'description' => trim($post['description'] ?? ''),
            'status'      => trim($post['status']      ?? 'active'),
            'start_date'  => trim($post['start_date']  ?? ''),
            'end_date'    => trim($post['end_date']    ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Project name is required.';
        } elseif (mb_strlen($data['name']) > 200) {
            $errors['name'] = 'Name must be 200 characters or fewer.';
        }

        if (!in_array($data['status'], $this->statuses, true)) {
            $errors['status'] = 'Invalid status value.';
        }

        if ($data['start_date'] !== '' && !strtotime($data['start_date'])) {
            $errors['start_date'] = 'Please enter a valid start date.';
        }

        if ($data['end_date'] !== '' && !strtotime($data['end_date'])) {
            $errors['end_date'] = 'Please enter a valid end date.';
        }

        if ($data['start_date'] !== '' && $data['end_date'] !== ''
            && strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors['end_date'] = 'End date must be on or after the start date.';
        }

        return $errors;
    }
}
