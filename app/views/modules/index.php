<div class="page-header">
    <h1 class="page-title">Modules</h1>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-puzzle-piece" style="color:var(--brand);margin-right:.4rem;"></i> Available Modules</h2>
    </div>
    <div class="card-body">
        <p style="color:var(--gray-600); font-size:.9rem; margin-bottom:1.5rem;">
            Enable or disable optional modules. Core modules (Contacts, Tasks, Projects) are always active.
        </p>

        <div style="display:flex; flex-direction:column; gap:1rem;">
            <?php foreach ($modules as $module): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem;
                        border:1px solid var(--gray-200); border-radius:var(--radius); background:<?= $module['enabled'] ? '#f0fdf4' : '#fff' ?>;">
                <div style="display:flex; align-items:center; gap:1rem;">
                    <div class="stat-icon <?= $module['enabled'] ? 'green' : 'blue' ?>" style="width:40px;height:40px;">
                        <i class="fa-solid <?= htmlspecialchars($module['icon']) ?>"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;"><?= htmlspecialchars($module['label']) ?></div>
                        <div style="font-size:.8rem; color:var(--gray-400);">
                            <?= $module['enabled']
                                ? '<span style="color:var(--success);"><i class="fa-solid fa-circle-check"></i> Enabled</span>'
                                : '<span style="color:var(--gray-400);"><i class="fa-solid fa-circle-xmark"></i> Disabled</span>' ?>
                        </div>
                    </div>
                </div>
                <form method="POST" action="/settings/modules/<?= htmlspecialchars($module['name']) ?>/toggle">
<?= csrf_field() ?>
                    <button type="submit" class="btn <?= $module['enabled'] ? 'btn-danger' : 'btn-success' ?>">
                        <?php if ($module['enabled']): ?>
                            <i class="fa-solid fa-toggle-on"></i> Disable
                        <?php else: ?>
                            <i class="fa-solid fa-toggle-off"></i> Enable
                        <?php endif; ?>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>

            <?php if (empty($modules)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-puzzle-piece empty-icon"></i>
                <p>No optional modules available yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
