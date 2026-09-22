<?php
function itErr(string $f, array $e): string {
    return isset($e[$f]) ? '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
function itOv(string $f, array $old, string $def = ''): string {
    return htmlspecialchars($old[$f] ?? $def);
}
?>
<div class="page-header">
    <h1 class="page-title">Add Asset</h1>
    <a href="/it_planning" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/it_planning/store" novalidate>

            <div class="form-section-label">Asset Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="<?= itOv('name', $old) ?>" required>
                    <?= itErr('name', $errors) ?>
                </div>
                <div class="form-group">
                    <label>Asset Type <span style="color:var(--danger)">*</span></label>
                    <select name="asset_type">
                        <?php foreach ($assetTypes as $t): ?>
                        <option value="<?= $t ?>" <?= itOv('asset_type', $old, 'other') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= itErr('asset_type', $errors) ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_number" value="<?= itOv('serial_number', $old) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= itOv('status', $old, 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section-label">Dates &amp; Assignment</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" value="<?= itOv('purchase_date', $old) ?>">
                </div>
                <div class="form-group">
                    <label>Warranty Expiry</label>
                    <input type="date" name="warranty_expiry" value="<?= itOv('warranty_expiry', $old) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Assigned To</label>
                    <select name="assigned_to">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($contacts as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= itOv('assigned_to', $old) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= itOv('location', $old) ?>" placeholder="e.g. Office A, Cabinet 3">
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= itOv('notes', $old) ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/it_planning" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Asset</button>
            </div>
        </form>
    </div>
</div>
