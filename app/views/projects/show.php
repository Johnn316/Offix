<?php $pid = $project['id']; ?>

<div class="page-header">
    <h1 class="page-title ie-title"
        data-model="project"
        data-id="<?= $pid ?>"
        data-field="name"
        data-original="<?= htmlspecialchars($project['name']) ?>"
        title="Double-click to edit">
        <?= htmlspecialchars($project['name']) ?>
    </h1>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="/projects/<?= $pid ?>/edit" class="btn btn-warning">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <button class="btn btn-danger"
                data-confirm-delete="true"
                data-action="/projects/<?= $pid ?>/delete">
            <i class="fa-solid fa-trash"></i> Delete
        </button>
        <a href="/projects" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> All Projects
        </a>
    </div>
</div>

<p style="font-size:.72rem; color:var(--gray-400); margin-bottom:1rem;">
    <i class="fa-solid fa-pencil" style="font-size:.65rem;"></i>
    Double-click any field to edit inline
</p>

<!-- Project Details -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><h2>Project Details</h2></div>
    <div class="card-body">
        <div class="detail-grid">

            <div class="detail-item">
                <label>Status</label>
                <p>
                    <span class="badge badge-<?= htmlspecialchars($project['status']) ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $project['status'])) ?>
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <label>Start Date</label>
                <p class="ie-field"
                   data-model="project" data-id="<?= $pid ?>" data-field="start_date" data-type="date"
                   data-value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                    <?= $project['start_date'] ? htmlspecialchars(date('M j, Y', strtotime($project['start_date']))) : '—' ?>
                </p>
            </div>

            <div class="detail-item">
                <label>End Date</label>
                <p class="ie-field"
                   data-model="project" data-id="<?= $pid ?>" data-field="end_date" data-type="date"
                   data-value="<?= htmlspecialchars($project['end_date'] ?? '') ?>">
                    <?= $project['end_date'] ? htmlspecialchars(date('M j, Y', strtotime($project['end_date']))) : '—' ?>
                </p>
            </div>

            <div class="detail-item">
                <label>Created</label>
                <p><?= htmlspecialchars(date('M j, Y', strtotime($project['created_at']))) ?></p>
            </div>

            <div class="detail-item full">
                <label>Description</label>
                <p class="ie-field"
                   data-model="project" data-id="<?= $pid ?>" data-field="description" data-type="textarea"
                   style="white-space:pre-line; min-height:2.5rem;">
                    <?= htmlspecialchars($project['description'] ?? '') ?: '—' ?>
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Associated Tasks -->
<div class="card">
    <div class="card-header">
        <h2>Tasks (<?= count($tasks) ?>)</h2>
        <a href="/tasks/create" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus"></i> New Task
        </a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-list-check empty-icon"></i>
            <p>No tasks linked to this project yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($task['status']) ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $task['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($task['priority']) ?>">
                                <?= htmlspecialchars($task['priority']) ?>
                            </span>
                        </td>
                        <td><?= $task['due_date'] ? htmlspecialchars($task['due_date']) : '—' ?></td>
                        <td>
                            <?php if ($task['contact_name']): ?>
                                <a href="/contacts/<?= $task['contact_id'] ?>">
                                    <?= htmlspecialchars($task['contact_name']) ?>
                                </a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/tasks/<?= $task['id'] ?>/edit" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <button class="btn btn-sm btn-danger"
                                        data-confirm-delete="true"
                                        data-action="/tasks/<?= $task['id'] ?>/delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
