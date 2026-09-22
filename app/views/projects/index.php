<div class="page-header">
    <h1 class="page-title"><?= __('projects.title') ?></h1>
    <a href="/projects/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <?= __('projects.new') ?></a>
</div>

<div class="card">
    <?php if (empty($projects)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-briefcase empty-icon"></i>
            <p><?= __('projects.empty') ?></p>
            <a href="/projects/create" class="btn btn-primary"><?= __('projects.new') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= __('label.name') ?></th>
                        <th><?= __('label.status') ?></th>
                        <th><?= __('label.start_date') ?></th>
                        <th><?= __('label.end_date') ?></th>
                        <th><?= __('label.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><a href="/projects/<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($p['status']) ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $p['status'])) ?>
                            </span>
                        </td>
                        <td><?= $p['start_date'] ? htmlspecialchars($p['start_date']) : '—' ?></td>
                        <td><?= $p['end_date']   ? htmlspecialchars($p['end_date'])   : '—' ?></td>
                        <td>
                            <div class="actions">
                                <a href="/projects/<?= $p['id'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fa-solid fa-eye"></i> <?= __('action.view') ?>
                                </a>
                                <a href="/projects/<?= $p['id'] ?>/edit" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen"></i> <?= __('action.edit') ?>
                                </a>
                                <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/projects/<?= $p['id'] ?>/delete">
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
