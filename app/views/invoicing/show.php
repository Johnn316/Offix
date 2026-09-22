<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($invoice['number']) ?></h1>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="/invoicing/<?= $invoice['id'] ?>/print" class="btn btn-secondary" target="_blank">
            <i class="fa-solid fa-print"></i> Print
        </a>
        <a href="/invoicing/<?= $invoice['id'] ?>/edit" class="btn btn-warning">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <button class="btn btn-danger" data-confirm-delete="true" data-action="/invoicing/<?= $invoice['id'] ?>/delete">
            <i class="fa-solid fa-trash"></i> Delete
        </button>
        <a href="/invoicing" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> All Invoices</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; align-items:start;">

    <!-- Line Items -->
    <div class="card">
        <div class="card-header"><h2>Line Items</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Unit Price</th>
                        <th style="text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td style="text-align:right;"><?= htmlspecialchars($item['quantity']) ?></td>
                        <td style="text-align:right;"><?= number_format($item['unit_price'], 2) ?></td>
                        <td style="text-align:right;"><?= number_format($item['amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="padding:1rem 1.25rem; display:flex; justify-content:flex-end;">
            <div style="display:flex; flex-direction:column; gap:.35rem; align-items:flex-end; min-width:220px;">
                <div style="display:flex; justify-content:space-between; width:100%; color:var(--gray-600); font-size:.9rem;">
                    <span>Subtotal</span><span><?= number_format($totals['subtotal'], 2) ?></span>
                </div>
                <?php if ($invoice['tax_rate'] > 0): ?>
                <div style="display:flex; justify-content:space-between; width:100%; color:var(--gray-600); font-size:.9rem;">
                    <span>Tax (<?= $invoice['tax_rate'] ?>%)</span><span><?= number_format($totals['tax'], 2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex; justify-content:space-between; width:100%; font-weight:700; font-size:1.1rem; border-top:2px solid var(--gray-200); padding-top:.5rem;">
                    <span>Total</span><span><?= number_format($totals['total'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="card">
        <div class="card-header"><h2>Details</h2></div>
        <div class="card-body">
            <div class="detail-grid" style="grid-template-columns:1fr;">
                <div class="detail-item">
                    <label>Status</label>
                    <p><span class="badge badge-<?= htmlspecialchars($invoice['status']) ?>"><?= htmlspecialchars($invoice['status']) ?></span></p>
                </div>
                <div class="detail-item">
                    <label>Client</label>
                    <p><?= $invoice['contact_name'] ? '<a href="/contacts/' . $invoice['contact_id'] . '">' . htmlspecialchars($invoice['contact_name']) . '</a>' : '—' ?></p>
                </div>
                <div class="detail-item">
                    <label>Project</label>
                    <p><?= $invoice['project_name'] ? '<a href="/projects/' . $invoice['project_id'] . '">' . htmlspecialchars($invoice['project_name']) . '</a>' : '—' ?></p>
                </div>
                <div class="detail-item">
                    <label>Issue Date</label>
                    <p><?= htmlspecialchars(date('M j, Y', strtotime($invoice['issue_date']))) ?></p>
                </div>
                <div class="detail-item">
                    <label>Due Date</label>
                    <p><?= $invoice['due_date'] ? htmlspecialchars(date('M j, Y', strtotime($invoice['due_date']))) : '—' ?></p>
                </div>
                <?php if ($invoice['notes']): ?>
                <div class="detail-item">
                    <label>Notes</label>
                    <p style="white-space:pre-line;"><?= htmlspecialchars($invoice['notes']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
