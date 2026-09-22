<div class="page-header">
    <h1 class="page-title"><?= __('tasks.title') ?></h1>
    <a href="/tasks/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <?= __('tasks.new') ?></a>
</div>

<div class="card">
    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-list-check empty-icon"></i>
            <p><?= __('tasks.empty') ?></p>
            <a href="/tasks/create" class="btn btn-primary"><?= __('tasks.new') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= __('label.name') ?></th>
                        <th><?= __('label.status') ?></th>
                        <th><?= __('label.priority') ?></th>
                        <th><?= __('label.due_date') ?></th>
                        <th><?= __('label.contact') ?></th>
                        <th><?= __('label.project') ?></th>
                        <th><?= __('label.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><a href="/tasks/<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></a></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($t['status']) ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $t['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($t['priority']) ?>">
                                <?= htmlspecialchars($t['priority']) ?>
                            </span>
                        </td>
                        <td><?= $t['due_date'] ? htmlspecialchars($t['due_date']) : '—' ?></td>
                        <td>
                            <?php if ($t['contact_name']): ?>
                                <a href="/contacts/<?= $t['contact_id'] ?>"><?= htmlspecialchars($t['contact_name']) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['project_name']): ?>
                                <a href="/projects/<?= $t['project_id'] ?>"><?= htmlspecialchars($t['project_name']) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/tasks/<?= $t['id'] ?>/edit" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen"></i> <?= __('action.edit') ?>
                                </a>
                                <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/tasks/<?= $t['id'] ?>/delete">
                                    <i class="fa-solid fa-trash"></i> <?= __('action.delete') ?>
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
