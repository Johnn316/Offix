<?php
function itErr(string $f, array $e): string {
    return isset($e[$f]) ? '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
?>
<div class="page-header">
    <h1 class="page-title">Edit Asset</h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/it_planning/<?= $asset['id'] ?>" class="btn btn-secondary"><i class="fa-solid fa-eye"></i> View</a>
        <a href="/it_planning" class="btn btn-secondary">All Assets</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/it_planning/<?= $asset['id'] ?>/update" novalidate>
<?= csrf_field() ?>

            <div class="form-section-label">Asset Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($asset['name']) ?>" required>
                    <?= itErr('name', $errors) ?>
                </div>
                <div class="form-group">
                    <label>Asset Type</label>
                    <select name="asset_type">
                        <?php foreach ($assetTypes as $t): ?>
                        <option value="<?= $t ?>" <?= $asset['asset_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_number" value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $asset['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section-label">Dates &amp; Assignment</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Warranty Expiry</label>
                    <input type="date" name="warranty_expiry" value="<?= htmlspecialchars($asset['warranty_expiry'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Assigned To</label>
                    <select name="assigned_to">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($contacts as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (string)$asset['assigned_to'] === (string)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($asset['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/it_planning/<?= $asset['id'] ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
        </form>
    </div>
</div>
