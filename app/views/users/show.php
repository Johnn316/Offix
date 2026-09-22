<?php $uid = (int) $user['id']; ?>

<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($user['name']) ?></h1>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="/users/<?= $uid ?>/edit" class="btn btn-warning">
            <i class="fa-solid fa-pen"></i> <?= __('action.edit') ?>
        </a>
        <?php if ($uid !== (int) $_SESSION['user_id']): ?>
        <button class="btn btn-danger"
                data-confirm-delete="true"
                data-action="/users/<?= $uid ?>/delete">
            <i class="fa-solid fa-trash"></i> <?= __('action.delete') ?>
        </button>
        <?php endif; ?>
        <a href="/users" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <?= __('users.title') ?>
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><h2><?= __('users.account_info') ?></h2></div>
    <div class="card-body">

        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
            <?php if ($user['avatar']): ?>
                <img src="/file/avatars/<?= htmlspecialchars($user['avatar']) ?>"
                     style="width:64px;height:64px;border-radius:50%;object-fit:cover;" alt="">
            <?php else: ?>
                <div style="width:64px;height:64px;border-radius:50%;background:var(--brand-light);
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:1.5rem;color:var(--brand);">
                    <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div>
                <div style="font-weight:600;"><?= htmlspecialchars($user['name']) ?></div>
                <div style="font-size:.85rem;color:var(--gray-400);"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>

        <div class="detail-grid">

            <div class="detail-item">
                <label><?= __('label.name') ?></label>
                <p><?= htmlspecialchars($user['name']) ?></p>
            </div>

            <div class="detail-item">
                <label><?= __('label.email') ?></label>
                <p><a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></p>
            </div>

            <div class="detail-item">
                <label><?= __('users.role') ?></label>
                <p>
                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'high' : ($user['role'] === 'manager' ? 'medium' : 'low') ?>">
                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <label><?= __('label.status') ?></label>
                <p>
                    <span class="badge <?= $user['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                        <?= $user['is_active'] ? __('users.active') : __('users.inactive') ?>
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <label><?= __('users.language') ?></label>
                <p><?= htmlspecialchars($user['language']) ?></p>
            </div>

            <div class="detail-item">
                <label><?= __('users.last_login') ?></label>
                <p><?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : '—' ?></p>
            </div>

            <div class="detail-item">
                <label><?= __('label.created') ?></label>
                <p><?= $user['created_at'] ? date('M j, Y', strtotime($user['created_at'])) : '—' ?></p>
            </div>

        </div>
    </div>
</div>
