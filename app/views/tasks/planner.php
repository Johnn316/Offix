<?php
function priorityBadge(string $p): string {
    return '<span class="badge badge-' . $p . '">' . $p . '</span>';
}
function statusBadge(string $s): string {
    return '<span class="badge badge-' . $s . '">' . str_replace('_', ' ', $s) . '</span>';
}
function recurrenceLabel(array $task): string {
    if (!$task['is_recurring'] || !$task['recurrence_type']) return '';
    $every = (int)$task['recurrence_interval'] > 1 ? ' every ' . $task['recurrence_interval'] : '';
    return '<span class="badge" style="background:#ede9fe;color:#6d28d9;" title="Recurring task">
        <i class="fa-solid fa-rotate"></i> ' . ucfirst($task['recurrence_type']) . $every . '
    </span>';
}
?>

<div class="page-header">
    <h1 class="page-title">Task Planner</h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/tasks/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Task</a>
        <a href="/tasks" class="btn btn-secondary"><i class="fa-solid fa-list"></i> List View</a>
    </div>
</div>

<!-- Overdue -->
<?php if (!empty($overdue)): ?>
<div class="card" style="margin-bottom:1.25rem; border-left:4px solid var(--danger);">
    <div class="card-header" style="background:#fff5f5;">
        <h2 style="color:var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i> Overdue (<?= count($overdue) ?>)
        </h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Task</th><th>Due</th><th>Priority</th><th>Status</th><th>Contact / Project</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($overdue as $t): ?>
                <tr>
                    <td>
                        <a href="/tasks/<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></a>
                        <?= recurrenceLabel($t) ?>
                    </td>
                    <td style="color:var(--danger); font-weight:600;"><?= htmlspecialchars($t['due_date']) ?></td>
                    <td><?= priorityBadge($t['priority']) ?></td>
                    <td><?= statusBadge($t['status']) ?></td>
                    <td>
                        <?= $t['contact_name'] ? htmlspecialchars($t['contact_name']) : '' ?>
                        <?= $t['project_name'] ? '<br><small>' . htmlspecialchars($t['project_name']) . '</small>' : '' ?>
                        <?= (!$t['contact_name'] && !$t['project_name']) ? '—' : '' ?>
                    </td>
                    <td>
                        <a href="/tasks/<?= $t['id'] ?>/edit" class="btn btn-sm btn-warning">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming grouped by date -->
<?php if (empty($grouped) && empty($overdue)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fa-solid fa-calendar-check empty-icon"></i>
            <p>No upcoming tasks. You're all caught up!</p>
            <a href="/tasks/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Task</a>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($grouped as $date => $dateTasks): ?>
    <?php
        $isToday    = $date === $today;
        $isTomorrow = $date === date('Y-m-d', strtotime('+1 day'));
        $label = match(true) {
            $date === 'No Date'  => 'No Due Date',
            $isToday             => 'Today — ' . date('M j, Y'),
            $isTomorrow          => 'Tomorrow — ' . date('M j, Y', strtotime('+1 day')),
            default              => date('l, M j, Y', strtotime($date)),
        };
        $headerStyle = $isToday ? 'border-left:4px solid var(--brand); background:#eff6ff;' : '';
    ?>
    <div class="card" style="margin-bottom:1.25rem; <?= $headerStyle ?>">
        <div class="card-header">
            <h2>
                <i class="fa-solid fa-calendar-day" style="color:var(--brand);margin-right:.4rem;"></i>
                <?= htmlspecialchars($label) ?>
                <span style="font-size:.8rem; font-weight:400; color:var(--gray-400); margin-left:.5rem;">
                    <?= count($dateTasks) ?> task<?= count($dateTasks) !== 1 ? 's' : '' ?>
                </span>
            </h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Task</th><th>Priority</th><th>Status</th><th>Contact / Project</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($dateTasks as $t): ?>
                    <tr>
                        <td>
                            <a href="/tasks/<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></a>
                            <?= recurrenceLabel($t) ?>
                        </td>
                        <td><?= priorityBadge($t['priority']) ?></td>
                        <td><?= statusBadge($t['status']) ?></td>
                        <td>
                            <?= $t['contact_name'] ? htmlspecialchars($t['contact_name']) : '' ?>
                            <?= $t['project_name'] ? '<br><small>' . htmlspecialchars($t['project_name']) . '</small>' : '' ?>
                            <?= (!$t['contact_name'] && !$t['project_name']) ? '—' : '' ?>
                        </td>
                        <td>
                            <a href="/tasks/<?= $t['id'] ?>/edit" class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
