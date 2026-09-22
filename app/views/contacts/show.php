<?php
function formatAddress(array $c): string {
    $parts = array_filter([
        $c['address1'] ?? '',
        $c['address2'] ?? '',
        trim(implode(', ', array_filter([$c['city'] ?? '', $c['state'] ?? '']))),
        trim(implode(' ', array_filter([$c['zip'] ?? '', $c['country'] ?? '']))),
    ]);
    return implode("\n", $parts);
}
$address = formatAddress($contact);
$cid = $contact['id'];
?>

<div class="page-header">
    <h1 class="page-title ie-title"
        data-model="contact"
        data-id="<?= $cid ?>"
        data-field="name"
        data-original="<?= htmlspecialchars($contact['name']) ?>"
        title="Double-click to edit">
        <?= htmlspecialchars($contact['name']) ?>
    </h1>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="/contacts/<?= $cid ?>/edit" class="btn btn-warning">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <button class="btn btn-danger"
                data-confirm-delete="true"
                data-action="/contacts/<?= $cid ?>/delete">
            <i class="fa-solid fa-trash"></i> Delete
        </button>
        <a href="/contacts" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> All Contacts
        </a>
    </div>
</div>

<p style="font-size:.72rem; color:var(--gray-400); margin-bottom:1rem;">
    <i class="fa-solid fa-pencil" style="font-size:.65rem;"></i>
    Double-click any field to edit inline
</p>

<!-- Contact Details -->
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><h2>Contact Details</h2></div>
    <div class="card-body">

        <div class="detail-grid">
            <div class="detail-item">
                <label>Full Name</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="name" data-type="text">
                    <?= htmlspecialchars($contact['name']) ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Company</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="company" data-type="text">
                    <?= htmlspecialchars($contact['company'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Email</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="email" data-type="email"
                   data-value="<?= htmlspecialchars($contact['email'] ?? '') ?>">
                    <?php if ($contact['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                            <?= htmlspecialchars($contact['email']) ?>
                        </a>
                    <?php else: ?>—<?php endif; ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Phone</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="phone" data-type="text">
                    <?= htmlspecialchars($contact['phone'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Added On</label>
                <p><?= htmlspecialchars(date('M j, Y', strtotime($contact['created_at']))) ?></p>
            </div>
        </div>

        <hr style="border:none; border-top:1px solid var(--gray-100); margin:1.25rem 0;">

        <p style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-400); margin-bottom:.75rem;">
            <i class="fa-solid fa-location-dot"></i> Address
        </p>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Address Line 1</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="address1" data-type="text">
                    <?= htmlspecialchars($contact['address1'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Address Line 2</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="address2" data-type="text">
                    <?= htmlspecialchars($contact['address2'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>City</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="city" data-type="text">
                    <?= htmlspecialchars($contact['city'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>State / Province</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="state" data-type="text">
                    <?= htmlspecialchars($contact['state'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>ZIP / Postal Code</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="zip" data-type="text">
                    <?= htmlspecialchars($contact['zip'] ?? '') ?: '—' ?>
                </p>
            </div>
            <div class="detail-item">
                <label>Country</label>
                <p class="ie-field"
                   data-model="contact" data-id="<?= $cid ?>" data-field="country" data-type="text">
                    <?= htmlspecialchars($contact['country'] ?? '') ?: '—' ?>
                </p>
            </div>
        </div>

        <hr style="border:none; border-top:1px solid var(--gray-100); margin:1.25rem 0;">
        <div class="detail-item">
            <label>Notes</label>
            <p class="ie-field"
               data-model="contact" data-id="<?= $cid ?>" data-field="notes" data-type="textarea"
               style="white-space:pre-line; min-height:2.5rem;">
                <?= htmlspecialchars($contact['notes'] ?? '') ?: '—' ?>
            </p>
        </div>

    </div>
</div>

<!-- Associated Tasks -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-list-check" style="color:var(--brand);margin-right:.4rem;"></i> Tasks</h2>
        <a href="/tasks/create" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus"></i> New Task
        </a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-list-check empty-icon"></i>
            <p>No tasks linked to this contact.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Project</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                        <td><span class="badge badge-<?= htmlspecialchars($task['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $task['status'])) ?></span></td>
                        <td><span class="badge badge-<?= htmlspecialchars($task['priority']) ?>"><?= htmlspecialchars($task['priority']) ?></span></td>
                        <td><?= $task['due_date'] ? htmlspecialchars($task['due_date']) : '—' ?></td>
                        <td><?= $task['project_name'] ? '<a href="/projects/' . $task['project_id'] . '">' . htmlspecialchars($task['project_name']) . '</a>' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
