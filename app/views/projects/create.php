<?php
function err(string $field, array $errors): string {
    if (!isset($errors[$field])) return '';
    return '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($errors[$field]) . '</span>';
}
function oldVal(string $field, array $old, string $default = ''): string {
    return htmlspecialchars($old[$field] ?? $default);
}
?>

<div class="page-header">
    <h1 class="page-title">New Project</h1>
    <a href="/projects" class="btn btn-secondary">← Back to Projects</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/projects/store" novalidate>

            <div class="form-grid">

                <div class="form-group full">
                    <label for="name">Project Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?= oldVal('name', $old) ?>" required maxlength="200">
                    <?= err('name', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>"
                                <?= oldVal('status', $old, 'active') === $s ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date"
                           value="<?= oldVal('start_date', $old) ?>">
                    <?= err('start_date', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date"
                           value="<?= oldVal('end_date', $old) ?>">
                    <?= err('end_date', $errors) ?>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?= oldVal('description', $old) ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Project</button>
                <a href="/projects" class="btn btn-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>
