<div class="page-header">
    <h1 class="page-title">Invoices</h1>
    <a href="/invoicing/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Invoice</a>
</div>

<div class="card">
    <?php if (empty($invoices)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-file-invoice-dollar empty-icon"></i>
            <p>No invoices yet.</p>
            <a href="/invoicing/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Invoice</a>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Number</th><th>Client</th><th>Project</th>
                    <th>Issue Date</th><th>Due Date</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><a href="/invoicing/<?= $inv['id'] ?>"><?= htmlspecialchars($inv['number']) ?></a></td>
                    <td><?= htmlspecialchars($inv['contact_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($inv['project_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($inv['issue_date']) ?></td>
                    <td><?= htmlspecialchars($inv['due_date'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($inv['status']) ?>"><?= htmlspecialchars($inv['status']) ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="/invoicing/<?= $inv['id'] ?>" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i></a>
                            <a href="/invoicing/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                            <a href="/invoicing/<?= $inv['id'] ?>/print" class="btn btn-sm btn-secondary"><i class="fa-solid fa-print"></i></a>
                            <button class="btn btn-sm btn-danger" data-confirm-delete="true" data-action="/invoicing/<?= $inv['id'] ?>/delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
