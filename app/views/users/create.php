<?php
function uErr(string $f, array $e): string {
    return isset($e[$f]) ? '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
function uOv(string $f, array $old, string $def = ''): string {
    return htmlspecialchars($old[$f] ?? $def);
}
?>
<div class="page-header">
    <h1 class="page-title"><?= __('users.new') ?></h1>
    <a href="/users" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('action.back') ?></a>
</div>

<div class="card" style="max-width:620px;">
    <div class="card-body">
        <form method="POST" action="/users/store" novalidate>

            <div class="form-section-label"><?= __('users.account_info') ?></div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= __('label.name') ?> *</label>
                    <input type="text" name="name" value="<?= uOv('name', $old) ?>" required>
                    <?= uErr('name', $errors) ?>
                </div>
                <div class="form-group">
                    <label><?= __('label.email') ?> *</label>
                    <input type="email" name="email" value="<?= uOv('email', $old) ?>" required>
                    <?= uErr('email', $errors) ?>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label><?= __('users.password') ?> *</label>
                <input type="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password">
                <?= uErr('password', $errors) ?>
            </div>

            <div class="form-section-label"><?= __('users.preferences') ?></div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= __('users.role') ?></label>
                    <select name="role">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r ?>" <?= uOv('role', $old, 'user') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= __('users.language') ?></label>
                    <select name="language">
                        <?php foreach ($languages as $code => $label): ?>
                        <option value="<?= $code ?>" <?= uOv('language', $old, 'en-US') === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?> (<?= $code ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= (($old['is_active'] ?? 1) ? 'checked' : '') ?>>
                    <?= __('users.active') ?>
                </label>
            </div>

            <div class="form-actions">
                <a href="/users" class="btn btn-secondary"><?= __('action.cancel') ?></a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= __('users.save') ?>
                </button>
            </div>
        </form>
    </div>
</div>
