<div class="page-header">
    <h1 class="page-title"><?= __('docs.versions') ?></h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/documents/<?= $doc['id'] ?>" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> <?= __('docs.open_editor') ?>
        </a>
        <a href="/documents" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <?= __('action.back') ?>
        </a>
    </div>
</div>

<div style="margin-bottom:1rem; color:var(--gray-500); font-size:.875rem;">
    <i class="fa-solid fa-file-lines" style="margin-right:.35rem; color:var(--brand);"></i>
    <strong><?= htmlspecialchars($doc['title']) ?></strong>
    &nbsp;·&nbsp; <?= __('docs.versions_hint') ?>
</div>

<div class="card">
    <?php if (empty($versions)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-clock-rotate-left empty-icon"></i>
            <p><?= __('docs.no_versions') ?></p>
            <p style="font-size:.85rem; color:var(--gray-400);"><?= __('docs.versions_auto_info') ?></p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= __('docs.saved_at') ?></th>
                        <th><?= __('docs.saved_by') ?></th>
                        <th><?= __('label.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versions as $i => $v): ?>
                    <tr>
                        <td style="color:var(--gray-400); font-size:.8rem;"><?= count($versions) - $i ?></td>
                        <td>
                            <?php if ($i === 0): ?>
                                <span class="badge badge-medium"><?= __('docs.latest') ?></span>
                            <?php endif; ?>
                            <?= date('M j, Y H:i:s', strtotime($v['created_at'])) ?>
                        </td>
                        <td><?= htmlspecialchars($v['created_by_name']) ?></td>
                        <td>
                            <form method="POST" action="/documents/<?= $doc['id'] ?>/restore">
                                <input type="hidden" name="version_id" value="<?= $v['id'] ?>">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-confirm-delete="true"
                                    data-action="/documents/<?= $doc['id'] ?>/restore"
                                    onclick="document.getElementById('restore-form-<?= $v['id'] ?>').submit();"
                                    style="display:none;">
                                </button>
                                <button type="submit" class="btn btn-secondary btn-sm"
                                    onclick="return confirm('<?= __('docs.restore_confirm') ?>')">
                                    <i class="fa-solid fa-rotate-left"></i> <?= __('docs.restore') ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
