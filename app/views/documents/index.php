<div class="page-header">
    <h1 class="page-title"><?= __('docs.title') ?></h1>
    <a href="/documents/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> <?= __('docs.new') ?>
    </a>
</div>

<div class="card">
    <?php if (empty($documents)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-file-lines empty-icon"></i>
            <p><?= __('docs.empty') ?></p>
            <a href="/documents/create" class="btn btn-primary"><?= __('docs.new') ?></a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= __('label.title') ?></th>
                        <th><?= __('docs.owner') ?></th>
                        <th><?= __('label.created') ?></th>
                        <th><?= __('docs.last_edited') ?></th>
                        <th><?= __('label.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td>
                            <a href="/documents/<?= $doc['id'] ?>" style="font-weight:500;">
                                <i class="fa-solid fa-file-lines" style="color:var(--brand); margin-right:.4rem;"></i>
                                <?= htmlspecialchars($doc['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($doc['owner_name']) ?></td>
                        <td style="color:var(--gray-400); font-size:.85rem;">
                            <?= date('M j, Y', strtotime($doc['created_at'])) ?>
                        </td>
                        <td style="color:var(--gray-400); font-size:.85rem;">
                            <?= date('M j, Y H:i', strtotime($doc['updated_at'])) ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:.35rem;">
                                <a href="/documents/<?= $doc['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-pen-to-square"></i> <?= __('action.edit') ?>
                                </a>
                                <a href="/documents/<?= $doc['id'] ?>/versions" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </a>
                                <?php if ($doc['owner_id'] == $_SESSION['user_id'] || ($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                <form method="POST" action="/documents/<?= $doc['id'] ?>/delete" style="display:inline;">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        data-confirm-delete="true"
                                        data-action="/documents/<?= $doc['id'] ?>/delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
