<?php
$typeIcons = [
    'laptop'  => 'fa-laptop',
    'desktop' => 'fa-desktop',
    'server'  => 'fa-server',
    'monitor' => 'fa-display',
    'phone'   => 'fa-mobile-screen',
    'tablet'  => 'fa-tablet-screen-button',
    'printer' => 'fa-print',
    'network' => 'fa-network-wired',
    'other'   => 'fa-microchip',
];
?>
<div class="page-header">
    <h1 class="page-title">IT Planning</h1>
    <a href="/it_planning/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Asset</a>
</div>

<?php if (!empty($expiring)): ?>
<div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:var(--radius); padding:1rem 1.25rem; margin-bottom:1.25rem;">
    <div style="font-weight:600; color:#9a3412; margin-bottom:.5rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> Warranty Expiring Soon (within 60 days)
    </div>
    <?php foreach ($expiring as $a): ?>
    <div style="font-size:.875rem; color:#7c2d12; margin-bottom:.2rem;">
        <a href="/it_planning/<?= $a['id'] ?>" style="color:inherit; font-weight:500;"><?= htmlspecialchars($a['name']) ?></a>
        — expires <?= htmlspecialchars($a['warranty_expiry']) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($grouped)): ?>
<div class="card">
    <div class="empty-state">
        <i class="fa-solid fa-server empty-icon"></i>
        <p>No IT assets yet.</p>
        <a href="/it_planning/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Asset</a>
    </div>
</div>
<?php else: ?>
    <?php foreach ($grouped as $type => $assets): ?>
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header" style="display:flex; align-items:center; gap:.6rem;">
            <i class="fa-solid <?= $typeIcons[$type] ?? 'fa-microchip' ?>" style="color:var(--brand);"></i>
            <h2><?= ucfirst(str_replace('_', ' ', $type)) ?>s</h2>
            <span class="badge" style="margin-left:auto;"><?= count($assets) ?></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th><th>Serial</th><th>Status</th>
                        <th>Assigned To</th><th>Location</th><th>Warranty</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $a): ?>
                    <?php
                        $warrantyClass = '';
                        if ($a['warranty_expiry']) {
                            $diff = (new DateTime($a['warranty_expiry']))->diff(new DateTime())->days;
                            $expired = strtotime($a['warranty_expiry']) < time();
                            if ($expired) $warrantyClass = 'color:#dc2626;font-weight:600;';
                            elseif ($diff <= 60) $warrantyClass = 'color:#d97706;font-weight:600;';
                        }
                    ?>
                    <tr>
                        <td><a href="/it_planning/<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></a></td>
                        <td style="color:var(--gray-400); font-size:.85rem; font-family:monospace;"><?= htmlspecialchars($a['serial_number'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= htmlspecialchars($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                        <td><?= htmlspecialchars($a['assigned_to_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($a['location'] ?? '—') ?></td>
                        <td style="<?= $warrantyClass ?>"><?= $a['warranty_expiry'] ? htmlspecialchars($a['warranty_expiry']) : '—' ?></td>
                        <td>
                            <div class="actions">
                                <a href="/it_planning/<?= $a['id'] ?>" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i></a>
                                <a href="/it_planning/<?= $a['id'] ?>/edit" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/it_planning/<?= $a['id'] ?>/delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
