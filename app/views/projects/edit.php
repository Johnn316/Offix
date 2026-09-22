<?php
function err(string $field, array $errors): string {
    if (!isset($errors[$field])) return '';
    return '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($errors[$field]) . '</span>';
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Project</h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/projects/<?= $project['id'] ?>" class="btn btn-secondary">← View</a>
        <a href="/projects" class="btn btn-secondary">All Projects</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/projects/<?= $project['id'] ?>/update" novalidate>

            <div class="form-grid">

                <div class="form-group full">
                    <label for="name">Project Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($project['name']) ?>" required maxlength="200">
                    <?= err('name', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>"
                                <?= $project['status'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date"
                           value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                    <?= err('start_date', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date"
                           value="<?= htmlspecialchars($project['end_date'] ?? '') ?>">
                    <?= err('end_date', $errors) ?>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/projects/<?= $project['id'] ?>" class="btn btn-secondary">Cancel</a>
            </div>

        </form>
    </div>
</div>
