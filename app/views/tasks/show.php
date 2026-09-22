<?php $tid = $task['id']; ?>

<div class="page-header">
    <h1 class="page-title ie-title"
        data-model="task"
        data-id="<?= $tid ?>"
        data-field="title"
        data-original="<?= htmlspecialchars($task['title']) ?>"
        title="Double-click to edit">
        <?= htmlspecialchars($task['title']) ?>
    </h1>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="/tasks/<?= $tid ?>/edit" class="btn btn-warning">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <button class="btn btn-danger"
                data-confirm-delete="true"
                data-action="/tasks/<?= $tid ?>/delete">
            <i class="fa-solid fa-trash"></i> Delete
        </button>
        <a href="/tasks" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> All Tasks
        </a>
    </div>
</div>

<p style="font-size:.72rem; color:var(--gray-400); margin-bottom:1rem;">
    <i class="fa-solid fa-pencil" style="font-size:.65rem;"></i>
    Double-click any field to edit inline
</p>

<div class="card">
    <div class="card-header"><h2>Task Details</h2></div>
    <div class="card-body">
        <div class="detail-grid">

            <div class="detail-item">
                <label>Status</label>
                <p>
                    <span class="badge badge-<?= htmlspecialchars($task['status']) ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $task['status'])) ?>
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <label>Priority</label>
                <p>
                    <span class="badge badge-<?= htmlspecialchars($task['priority']) ?>">
                        <?= htmlspecialchars($task['priority']) ?>
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <label>Due Date</label>
                <p class="ie-field"
                   data-model="task" data-id="<?= $tid ?>" data-field="due_date" data-type="date"
                   data-value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">
                    <?= $task['due_date'] ? htmlspecialchars(date('M j, Y', strtotime($task['due_date']))) : '—' ?>
                </p>
            </div>

            <div class="detail-item">
                <label>Created</label>
                <p><?= htmlspecialchars(date('M j, Y', strtotime($task['created_at']))) ?></p>
            </div>

            <div class="detail-item">
                <label>Linked Contact</label>
                <p>
                    <?php if ($task['contact_name']): ?>
                        <a href="/contacts/<?= $task['contact_id'] ?>">
                            <?= htmlspecialchars($task['contact_name']) ?>
                        </a>
                    <?php else: ?>—<?php endif; ?>
                </p>
            </div>

            <div class="detail-item">
                <label>Linked Project</label>
                <p>
                    <?php if ($task['project_name']): ?>
                        <a href="/projects/<?= $task['project_id'] ?>">
                            <?= htmlspecialchars($task['project_name']) ?>
                        </a>
                    <?php else: ?>—<?php endif; ?>
                </p>
            </div>

            <div class="detail-item full">
                <label>Description</label>
                <p class="ie-field"
                   data-model="task" data-id="<?= $tid ?>" data-field="description" data-type="textarea"
                   style="white-space:pre-line; min-height:2.5rem;">
                    <?= htmlspecialchars($task['description'] ?? '') ?: '—' ?>
                </p>
            </div>

        </div>
    </div>
</div>
