<?php
function statusBadge(string $status): string {
    return '<span class="badge badge-' . htmlspecialchars($status) . '">'
         . htmlspecialchars(str_replace('_', ' ', $status))
         . '</span>';
}
function priorityBadge(string $p): string {
    return '<span class="badge badge-' . htmlspecialchars($p) . '">' . htmlspecialchars($p) . '</span>';
}
?>

<div class="stat-grid">
    <div class="stat-box">
        <div class="stat-icon blue"><i class="fa-solid fa-address-book"></i></div>
        <div class="stat-info">
            <p><?= __('dashboard.contacts') ?></p>
            <h3><?= $counts['contacts'] ?></h3>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon green"><i class="fa-solid fa-briefcase"></i></div>
        <div class="stat-info">
            <p><?= __('dashboard.projects') ?></p>
            <h3><?= $counts['projects'] ?></h3>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon yellow"><i class="fa-solid fa-list-check"></i></div>
        <div class="stat-info">
            <p><?= __('dashboard.total_tasks') ?></p>
            <h3><?= $counts['tasks'] ?></h3>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon red"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
            <p><?= __('dashboard.open_tasks') ?></p>
            <h3><?= $counts['tasks_pending'] ?></h3>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">

    <div class="card">
        <div class="card-header">
            <h2><?= __('dashboard.recent_tasks') ?></h2>
            <a href="/tasks/create" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> <?= __('action.new') ?></a>
        </div>
        <?php if (empty($recentTasks)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-list-check empty-icon"></i>
                <p><?= __('tasks.empty') ?></p>
                <a href="/tasks/create" class="btn btn-sm btn-primary"><?= __('tasks.new') ?></a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><?= __('label.name') ?></th>
                            <th><?= __('label.priority') ?></th>
                            <th><?= __('label.status') ?></th>
                            <th><?= __('label.due_date') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTasks as $task): ?>
                        <tr>
                            <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                            <td><?= priorityBadge($task['priority']) ?></td>
                            <td><?= statusBadge($task['status']) ?></td>
                            <td><?= $task['due_date'] ? htmlspecialchars($task['due_date']) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><?= __('dashboard.recent_contacts') ?></h2>
            <a href="/contacts/create" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> <?= __('action.new') ?></a>
        </div>
        <?php if (empty($recentContacts)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-address-book empty-icon"></i>
                <p><?= __('contacts.empty') ?></p>
                <a href="/contacts/create" class="btn btn-sm btn-primary"><?= __('contacts.new') ?></a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><?= __('label.name') ?></th>
                            <th><?= __('label.company') ?></th>
                            <th><?= __('label.email') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentContacts as $contact): ?>
                        <tr>
                            <td><a href="/contacts/<?= $contact['id'] ?>"><?= htmlspecialchars($contact['name']) ?></a></td>
                            <td><?= htmlspecialchars($contact['company'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($contact['email'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<div style="margin-top:1.5rem; display:flex; gap:.75rem; flex-wrap:wrap;">
    <a href="/contacts" class="btn btn-secondary"><i class="fa-solid fa-address-book"></i> <?= __('dashboard.all_contacts') ?></a>
    <a href="/tasks"    class="btn btn-secondary"><i class="fa-solid fa-list-check"></i> <?= __('dashboard.all_tasks') ?></a>
    <a href="/projects" class="btn btn-secondary"><i class="fa-solid fa-briefcase"></i> <?= __('dashboard.all_projects') ?></a>
</div>
