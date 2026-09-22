<?php
function pErr(string $f, array $e): string {
    return isset($e[$f]) ? '<span class="field-error">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
?>
<div class="page-header">
    <h1 class="page-title"><?= __('profile.settings') ?></h1>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; align-items:start;">

    <!-- Left card: profile details + password -->
    <div class="card">
        <div class="card-header"><h2><?= __('profile.details') ?></h2></div>
        <div class="card-body">
            <form method="POST" action="/profile/update" enctype="multipart/form-data" novalidate>

                <!-- Avatar -->
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; padding-bottom:1.25rem; border-bottom:1px solid var(--gray-100);">
                    <div id="avatar-preview" style="width:68px;height:68px;border-radius:50%;overflow:hidden;border:2px solid var(--gray-200);flex-shrink:0;">
                        <?php if ($user['avatar']): ?>
                            <img src="/file/avatars/<?= htmlspecialchars($user['avatar']) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--brand-light);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.4rem;color:var(--brand);">
                                <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                            <i class="fa-solid fa-camera"></i> <?= __('profile.change_photo') ?>
                            <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">
                        </label>
                        <div style="font-size:.72rem;color:var(--gray-400);margin-top:.3rem;">JPEG · PNG · WebP &mdash; max 2 MB</div>
                        <?= pErr('avatar', $errors) ?>
                    </div>
                </div>

                <!-- Name + Email -->
                <div class="form-group">
                    <label><?= __('label.name') ?> <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    <?= pErr('name', $errors) ?>
                </div>
                <div class="form-group">
                    <label><?= __('label.email') ?> <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    <?= pErr('email', $errors) ?>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label><?= __('users.language') ?></label>
                    <select name="language" style="max-width:240px;">
                        <?php foreach ($languages as $code => $label): ?>
                        <option value="<?= $code ?>" <?= $user['language'] === $code ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?> (<?= $code ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Change password -->
                <div style="padding-top:1.1rem; border-top:1px solid var(--gray-100); margin-bottom:1rem;">
                    <div class="form-section-label" style="margin-top:0;"><?= __('profile.change_password') ?></div>
                    <p style="font-size:.8rem;color:var(--gray-400);margin:-0.25rem 0 1rem;">Leave all three fields blank to keep your current password.</p>
                </div>

                <div style="max-width:320px;">
                    <div class="form-group">
                        <label><?= __('profile.current_password') ?></label>
                        <input type="password" name="password_current" autocomplete="current-password">
                        <?= pErr('password_current', $errors) ?>
                    </div>
                    <div class="form-group">
                        <label><?= __('profile.new_password') ?></label>
                        <input type="password" name="password_new" autocomplete="new-password" placeholder="Min. 8 characters">
                        <?= pErr('password_new', $errors) ?>
                    </div>
                    <div class="form-group" style="margin-bottom:1.5rem;">
                        <label><?= __('profile.confirm_password') ?></label>
                        <input type="password" name="password_confirm" autocomplete="new-password">
                        <?= pErr('password_confirm', $errors) ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= __('profile.save') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Right card: account info -->
    <div class="card">
        <div class="card-header"><h2><?= __('profile.account') ?></h2></div>
        <div class="card-body">
            <div class="detail-grid" style="grid-template-columns:1fr;">
                <div class="detail-item">
                    <label><?= __('users.role') ?></label>
                    <p>
                        <span class="badge badge-<?= $user['role'] === 'admin' ? 'high' : ($user['role'] === 'manager' ? 'medium' : 'low') ?>">
                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                        </span>
                    </p>
                </div>
                <div class="detail-item">
                    <label><?= __('users.last_login') ?></label>
                    <p><?= $user['last_login'] ? htmlspecialchars(date('M j, Y H:i', strtotime($user['last_login']))) : '—' ?></p>
                </div>
                <div class="detail-item">
                    <label><?= __('label.created') ?></label>
                    <p><?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatar-preview').innerHTML =
            `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
