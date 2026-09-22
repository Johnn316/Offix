<div class="page-header">
    <h1 class="page-title"><?= __('users.title') ?></h1>
    <a href="/users/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <?= __('users.new') ?></a>
</div>

<div class="card">
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-users empty-icon"></i>
            <p><?= __('users.empty') ?></p>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?= __('label.name') ?></th>
                    <th><?= __('label.email') ?></th>
                    <th><?= __('users.role') ?></th>
                    <th><?= __('users.language') ?></th>
                    <th><?= __('users.last_login') ?></th>
                    <th><?= __('label.status') ?></th>
                    <th><?= __('label.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:.6rem;">
                            <?php if ($u['avatar']): ?>
                                <img src="/file/avatars/<?= htmlspecialchars($u['avatar']) ?>"
                                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                            <?php else: ?>
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--brand-light);
                                            display:flex;align-items:center;justify-content:center;
                                            font-weight:700;font-size:.8rem;color:var(--brand);">
                                    <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <a href="/users/<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></a>
                            <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                                <span style="font-size:.7rem;color:var(--gray-400);">(you)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'high' : ($u['role'] === 'manager' ? 'medium' : 'low') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= htmlspecialchars($u['language']) ?></td>
                    <td style="color:var(--gray-400); font-size:.85rem;"><?= $u['last_login'] ? date('M j, Y H:i', strtotime($u['last_login'])) : '—' ?></td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $u['is_active'] ? __('users.active') : __('users.inactive') ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-pen"></i> <?= __('action.edit') ?>
                            </a>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                            <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/users/<?= $u['id'] ?>/delete">
                                <i class="fa-solid fa-trash"></i> <?= __('action.delete') ?>
                            </button>
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
