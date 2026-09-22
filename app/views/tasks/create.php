<?php
function err(string $field, array $errors): string {
    if (!isset($errors[$field])) return '';
    return '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($errors[$field]) . '</span>';
}
function oldVal(string $field, array $old, string $default = ''): string {
    return htmlspecialchars($old[$field] ?? $default);
}
$isRecurring = ($old['is_recurring'] ?? 0) == 1;
?>

<div class="page-header">
    <h1 class="page-title">New Task</h1>
    <a href="/tasks" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Tasks</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/tasks/store" novalidate>
<?= csrf_field() ?>

            <div class="form-section-label">Task Details</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="title">Title <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="title" name="title"
                           value="<?= oldVal('title', $old) ?>" required maxlength="255">
                    <?= err('title', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= oldVal('status', $old, 'pending') === $s ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?= $p ?>" <?= oldVal('priority', $old, 'medium') === $p ? 'selected' : '' ?>>
                                <?= ucfirst($p) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?= oldVal('due_date', $old) ?>">
                    <?= err('due_date', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="contact_id">Linked Contact</label>
                    <select id="contact_id" name="contact_id">
                        <option value="">— None —</option>
                        <?php foreach ($contacts as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= oldVal('contact_id', $old) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="project_id">Linked Project</label>
                    <select id="project_id" name="project_id">
                        <option value="">— None —</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= oldVal('project_id', $old) == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= oldVal('description', $old) ?></textarea>
                </div>
            </div>

            <!-- Recurrence -->
            <div class="form-section-label" style="margin-top:1.5rem;">
                <label style="display:flex; align-items:center; gap:.6rem; cursor:pointer; text-transform:none; letter-spacing:0; font-size:.9rem; font-weight:600; color:var(--gray-600);">
                    <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                           <?= $isRecurring ? 'checked' : '' ?>
                           onchange="document.getElementById('recurrence-panel').style.display = this.checked ? 'block' : 'none'">
                    <i class="fa-solid fa-rotate"></i> Make this a recurring task
                </label>
            </div>

            <div id="recurrence-panel" style="display:<?= $isRecurring ? 'block' : 'none' ?>;">
                <div class="form-grid" style="margin-top:.75rem;">
                    <div class="form-group">
                        <label for="recurrence_type">Repeats</label>
                        <select id="recurrence_type" name="recurrence_type">
                            <option value="">— Select —</option>
                            <?php foreach ($recurrenceTypes as $rt): ?>
                                <option value="<?= $rt ?>" <?= oldVal('recurrence_type', $old) === $rt ? 'selected' : '' ?>>
                                    <?= ucfirst($rt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= err('recurrence_type', $errors) ?>
                    </div>

                    <div class="form-group">
                        <label for="recurrence_interval">Every</label>
                        <input type="number" id="recurrence_interval" name="recurrence_interval"
                               value="<?= oldVal('recurrence_interval', $old, '1') ?>" min="1" max="365">
                        <span style="font-size:.8rem; color:var(--gray-400);">occurrence(s)</span>
                    </div>

                    <div class="form-group">
                        <label for="recurrence_end_date">End Date <span style="color:var(--gray-400)">(optional)</span></label>
                        <input type="date" id="recurrence_end_date" name="recurrence_end_date"
                               value="<?= oldVal('recurrence_end_date', $old) ?>">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Task
                </button>
                <a href="/tasks" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
