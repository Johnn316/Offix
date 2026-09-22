<div class="page-header">
    <h1 class="page-title"><?= __('contacts.title') ?></h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/contacts/import" class="btn btn-secondary"><i class="fa-solid fa-file-import"></i> <?= __('contacts.import') ?></a>
        <a href="/contacts/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <?= __('contacts.new') ?></a>
    </div>
</div>

<div class="card">
    <?php if (empty($contacts)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-address-book empty-icon"></i>
            <p><?= __('contacts.empty') ?></p>
            <a href="/contacts/create" class="btn btn-primary"><?= __('contacts.new') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= __('label.name') ?></th>
                        <th><?= __('label.email') ?></th>
                        <th><?= __('label.phone') ?></th>
                        <th><?= __('label.company') ?></th>
                        <th><?= __('label.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><a href="/contacts/<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></td>
                        <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['company'] ?? '—') ?></td>
                        <td>
                            <div class="actions">
                                <a href="/contacts/<?= $c['id'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fa-solid fa-eye"></i> <?= __('action.view') ?>
                                </a>
                                <a href="/contacts/<?= $c['id'] ?>/edit" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen"></i> <?= __('action.edit') ?>
                                </a>
                                <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/contacts/<?= $c['id'] ?>/delete">
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
