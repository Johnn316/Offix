<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\Contact;
use App\Models\Project;

class TaskController extends Controller
{
    private Task  $model;
    private array $statuses   = ['pending', 'in_progress', 'done'];
    private array $priorities = ['low', 'medium', 'high'];
    private array $recurrenceTypes = ['daily', 'weekly', 'monthly', 'yearly'];

    public function __construct()
    {
        $this->model = new Task();
    }

    // ── List ───────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->render('tasks.index', [
            'pageTitle' => __('tasks.title'),
            'tasks'     => $this->model->all(),
        ]);
    }

    // ── Task Planner ───────────────────────────────────────────────────────
    public function planner(): void
    {
        $tasks = $this->model->upcoming();

        // Group by due date for timeline display
        $grouped = [];
        $overdue  = [];
        $today    = date('Y-m-d');

        foreach ($tasks as $task) {
            if (!$task['due_date']) {
                $grouped['No Date'][] = $task;
            } elseif ($task['due_date'] < $today) {
                $overdue[] = $task;
            } else {
                $grouped[$task['due_date']][] = $task;
            }
        }

        ksort($grouped);

        $this->render('tasks.planner', [
            'pageTitle' => __('tasks.planner'),
            'grouped'   => $grouped,
            'overdue'   => $overdue,
            'today'     => $today,
        ]);
    }

    // ── Create form ────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->render('tasks.create', [
            'pageTitle'      => __('tasks.new'),
            'contacts'       => (new Contact())->all(),
            'projects'       => (new Project())->all(),
            'statuses'       => $this->statuses,
            'priorities'     => $this->priorities,
            'recurrenceTypes'=> $this->recurrenceTypes,
            'errors'         => [],
            'old'            => [],
        ]);
    }

    // ── Store (POST) ───────────────────────────────────────────────────────
    public function store(): void
    {
        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('tasks.create', [
                'pageTitle'       => __('tasks.new'),
                'contacts'        => (new Contact())->all(),
                'projects'        => (new Project())->all(),
                'statuses'        => $this->statuses,
                'priorities'      => $this->priorities,
                'recurrenceTypes' => $this->recurrenceTypes,
                'errors'          => $errors,
                'old'             => $data,
            ]);
            return;
        }

        $id = $this->model->create($data);
        $_SESSION['flash_success'] = 'Task created successfully.';
        $this->redirect('/tasks/' . $id);
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(string $id): void
    {
        $task = $this->model->find((int) $id);
        if (!$task) $this->abort(404, 'Task not found');

        $this->render('tasks.show', [
            'pageTitle' => $task['title'],
            'task'      => $task,
        ]);
    }

    // ── Edit form ──────────────────────────────────────────────────────────
    public function edit(string $id): void
    {
        $task = $this->model->find((int) $id);
        if (!$task) $this->abort(404, 'Task not found');

        $this->render('tasks.edit', [
            'pageTitle'       => 'Edit — ' . $task['title'],
            'task'            => $task,
            'contacts'        => (new Contact())->all(),
            'projects'        => (new Project())->all(),
            'statuses'        => $this->statuses,
            'priorities'      => $this->priorities,
            'recurrenceTypes' => $this->recurrenceTypes,
            'errors'          => [],
        ]);
    }

    // ── Update (POST) ──────────────────────────────────────────────────────
    public function update(string $id): void
    {
        $task = $this->model->find((int) $id);
        if (!$task) $this->abort(404, 'Task not found');

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('tasks.edit', [
                'pageTitle'       => 'Edit — ' . $task['title'],
                'task'            => array_merge($task, $data),
                'contacts'        => (new Contact())->all(),
                'projects'        => (new Project())->all(),
                'statuses'        => $this->statuses,
                'priorities'      => $this->priorities,
                'recurrenceTypes' => $this->recurrenceTypes,
                'errors'          => $errors,
            ]);
            return;
        }

        $wasNotDone = $task['status'] !== 'done';
        $this->model->update((int) $id, $data);

        // Auto-spawn next occurrence when a recurring task is marked done
        if ($wasNotDone && $data['status'] === 'done' && $data['is_recurring'] && $data['recurrence_type']) {
            $nextDate = Task::nextDueDate(
                $data['due_date'] ?: date('Y-m-d'),
                $data['recurrence_type'],
                (int) $data['recurrence_interval'],
                $data['recurrence_end_date'] ?: null
            );

            if ($nextDate) {
                $this->model->create(array_merge($data, [
                    'status'         => 'pending',
                    'due_date'       => $nextDate,
                    'parent_task_id' => $task['parent_task_id'] ?: $id,
                ]));
                $_SESSION['flash_success'] = 'Task completed. Next occurrence scheduled for ' . date('M j, Y', strtotime($nextDate)) . '.';
                $this->redirect('/tasks/planner');
                return;
            }
        }

        $_SESSION['flash_success'] = 'Task updated successfully.';
        $this->redirect('/tasks/' . $id);
    }

    // ── Delete (POST) ──────────────────────────────────────────────────────
    public function delete(string $id): void
    {
        $task = $this->model->find((int) $id);
        if (!$task) $this->abort(404, 'Task not found');

        $this->model->delete((int) $id);
        $_SESSION['flash_success'] = 'Task deleted.';
        $this->redirect('/tasks');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function sanitize(array $post): array
    {
        $isRecurring = isset($post['is_recurring']) && $post['is_recurring'] === '1';
        return [
            'title'               => trim($post['title']               ?? ''),
            'description'         => trim($post['description']         ?? ''),
            'due_date'            => trim($post['due_date']            ?? ''),
            'status'              => trim($post['status']              ?? 'pending'),
            'priority'            => trim($post['priority']            ?? 'medium'),
            'contact_id'          => trim($post['contact_id']          ?? ''),
            'project_id'          => trim($post['project_id']          ?? ''),
            'is_recurring'        => $isRecurring ? 1 : 0,
            'recurrence_type'     => $isRecurring ? trim($post['recurrence_type']     ?? '') : '',
            'recurrence_interval' => $isRecurring ? max(1, (int)($post['recurrence_interval'] ?? 1)) : 1,
            'recurrence_end_date' => $isRecurring ? trim($post['recurrence_end_date'] ?? '') : '',
            'parent_task_id'      => '',
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be 255 characters or fewer.';
        }

        if (!in_array($data['status'], $this->statuses, true)) {
            $errors['status'] = 'Invalid status value.';
        }

        if (!in_array($data['priority'], $this->priorities, true)) {
            $errors['priority'] = 'Invalid priority value.';
        }

        if ($data['due_date'] !== '' && !strtotime($data['due_date'])) {
            $errors['due_date'] = 'Please enter a valid date.';
        }

        if ($data['is_recurring'] && empty($data['recurrence_type'])) {
            $errors['recurrence_type'] = 'Please select a recurrence type.';
        }

        return $errors;
    }
}
