<?php
$typeIcons = [
    'laptop'=>'fa-laptop','desktop'=>'fa-desktop','server'=>'fa-server','monitor'=>'fa-display',
    'phone'=>'fa-mobile-screen','tablet'=>'fa-tablet-screen-button','printer'=>'fa-print',
    'network'=>'fa-network-wired','other'=>'fa-microchip',
];
$statusColors = ['active'=>'green','inactive'=>'blue','maintenance'=>'yellow','retired'=>'red'];
?>
<div class="page-header">
    <h1 class="page-title">
        <i class="fa-solid <?= $typeIcons[$asset['asset_type']] ?? 'fa-microchip' ?>" style="color:var(--brand);margin-right:.4rem;"></i>
        <?= htmlspecialchars($asset['name']) ?>
    </h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/it_planning/<?= $asset['id'] ?>/edit" class="btn btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
        <button class="btn btn-danger" data-confirm-delete="true" data-action="/it_planning/<?= $asset['id'] ?>/delete">
            <i class="fa-solid fa-trash"></i> Delete
        </button>
        <a href="/it_planning" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> All Assets</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <label>Asset Type</label>
                <p><?= ucfirst(htmlspecialchars($asset['asset_type'])) ?></p>
            </div>
            <div class="detail-item">
                <label>Status</label>
                <p><span class="badge badge-<?= htmlspecialchars($asset['status']) ?>"><?= htmlspecialchars($asset['status']) ?></span></p>
            </div>
            <div class="detail-item">
                <label>Serial Number</label>
                <p style="font-family:monospace;"><?= htmlspecialchars($asset['serial_number'] ?? '—') ?></p>
            </div>
            <div class="detail-item">
                <label>Assigned To</label>
                <p><?= $asset['assigned_to_name'] ? '<a href="/contacts/' . $asset['assigned_to'] . '">' . htmlspecialchars($asset['assigned_to_name']) . '</a>' : '—' ?></p>
            </div>
            <div class="detail-item">
                <label>Location</label>
                <p><?= htmlspecialchars($asset['location'] ?? '—') ?></p>
            </div>
            <div class="detail-item">
                <label>Purchase Date</label>
                <p><?= $asset['purchase_date'] ? htmlspecialchars(date('M j, Y', strtotime($asset['purchase_date']))) : '—' ?></p>
            </div>
            <div class="detail-item">
                <label>Warranty Expiry</label>
                <?php
                    $wExpiry = $asset['warranty_expiry'];
                    if ($wExpiry) {
                        $expired = strtotime($wExpiry) < time();
                        $diff    = (new DateTime($wExpiry))->diff(new DateTime())->days;
                        $style   = $expired ? 'color:var(--danger); font-weight:600;' : ($diff <= 60 ? 'color:#d97706; font-weight:600;' : '');
                    }
                ?>
                <p style="<?= $wExpiry ? ($style ?? '') : '' ?>">
                    <?= $wExpiry ? htmlspecialchars(date('M j, Y', strtotime($wExpiry))) : '—' ?>
                    <?php if ($wExpiry && $expired ?? false): ?> <span style="font-size:.8rem;">(Expired)</span><?php endif; ?>
                </p>
            </div>
            <?php if ($asset['notes']): ?>
            <div class="detail-item" style="grid-column:1/-1;">
                <label>Notes</label>
                <p style="white-space:pre-line;"><?= htmlspecialchars($asset['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
