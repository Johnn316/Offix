<?php
function err(string $f, array $e): string {
    return isset($e[$f]) ? '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
?>
<div class="page-header">
    <h1 class="page-title">Edit Invoice</h1>
    <div style="display:flex; gap:.5rem;">
        <a href="/invoicing/<?= $invoice['id'] ?>" class="btn btn-secondary"><i class="fa-solid fa-eye"></i> View</a>
        <a href="/invoicing" class="btn btn-secondary">All Invoices</a>
    </div>
</div>

<form method="POST" action="/invoicing/<?= $invoice['id'] ?>/update" novalidate>
<?= csrf_field() ?>
<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; align-items:start;">

    <div class="card">
        <div class="card-header"><h2>Line Items</h2></div>
        <div class="card-body">
            <table id="items-table" style="width:100%; font-size:.9rem; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:.5rem; color:var(--gray-600); font-size:.78rem; text-transform:uppercase;">Description</th>
                        <th style="text-align:right; padding:.5rem; width:80px; color:var(--gray-600); font-size:.78rem; text-transform:uppercase;">Qty</th>
                        <th style="text-align:right; padding:.5rem; width:110px; color:var(--gray-600); font-size:.78rem; text-transform:uppercase;">Unit Price</th>
                        <th style="text-align:right; padding:.5rem; width:100px; color:var(--gray-600); font-size:.78rem; text-transform:uppercase;">Amount</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <?php foreach ($items as $i => $item): ?>
                    <tr class="item-row">
                        <td style="padding:.35rem;"><input type="text" name="items[<?= $i ?>][description]" value="<?= htmlspecialchars($item['description']) ?>" style="width:100%;"></td>
                        <td style="padding:.35rem;"><input type="number" name="items[<?= $i ?>][quantity]" value="<?= htmlspecialchars($item['quantity']) ?>" min="0" step="0.01" class="item-qty" style="width:100%;text-align:right;"></td>
                        <td style="padding:.35rem;"><input type="number" name="items[<?= $i ?>][unit_price]" value="<?= htmlspecialchars($item['unit_price']) ?>" min="0" step="0.01" class="item-price" style="width:100%;text-align:right;"></td>
                        <td style="padding:.35rem;text-align:right;" class="item-amount"><?= number_format($item['amount'], 2) ?></td>
                        <td style="padding:.35rem;"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa-solid fa-xmark"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" id="add-row" class="btn btn-secondary btn-sm" style="margin-top:.75rem;">
                <i class="fa-solid fa-plus"></i> Add Line
            </button>
            <div style="margin-top:1.25rem; border-top:1px solid var(--gray-100); padding-top:1rem;">
                <div style="display:flex; justify-content:flex-end; flex-direction:column; align-items:flex-end; gap:.35rem;">
                    <div style="display:flex; gap:2rem;"><span style="color:var(--gray-600);">Subtotal</span><span id="subtotal">0.00</span></div>
                    <div style="display:flex; gap:2rem; align-items:center;"><span style="color:var(--gray-600);">Tax %</span>
                        <input type="number" name="tax_rate" id="tax_rate" value="<?= htmlspecialchars($invoice['tax_rate']) ?>" min="0" max="100" step="0.1" style="width:70px;text-align:right;" oninput="calcTotals()"></div>
                    <div style="display:flex; gap:2rem;"><span style="color:var(--gray-600);">Tax</span><span id="tax-amt">0.00</span></div>
                    <div style="display:flex; gap:2rem; font-weight:700; font-size:1.05rem; border-top:1px solid var(--gray-200); padding-top:.5rem;"><span>Total</span><span id="total">0.00</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Details</h2></div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom:.75rem;"><label>Invoice #</label>
                <input type="text" name="number" value="<?= htmlspecialchars($invoice['number']) ?>" required><?= err('number', $errors) ?></div>
            <div class="form-group" style="margin-bottom:.75rem;"><label>Status</label>
                <select name="status"><?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $invoice['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?></select></div>
            <div class="form-group" style="margin-bottom:.75rem;"><label>Client</label>
                <select name="contact_id"><option value="">— None —</option>
                    <?php foreach ($contacts as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (string)$invoice['contact_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?></select></div>
            <div class="form-group" style="margin-bottom:.75rem;"><label>Project</label>
                <select name="project_id"><option value="">— None —</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (string)$invoice['project_id'] === (string)$p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?></select></div>
            <div class="form-group" style="margin-bottom:.75rem;"><label>Issue Date</label>
                <input type="date" name="issue_date" value="<?= htmlspecialchars($invoice['issue_date']) ?>" required><?= err('issue_date', $errors) ?></div>
            <div class="form-group" style="margin-bottom:.75rem;"><label>Due Date</label>
                <input type="date" name="due_date" value="<?= htmlspecialchars($invoice['due_date'] ?? '') ?>"></div>
            <div class="form-group" style="margin-bottom:1rem;"><label>Notes</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($invoice['notes'] ?? '') ?></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
    </div>
</div>
</form>

<script>
let rowIndex = <?= count($items) ?>;
function calcTotals() {
    let sub = 0;
    document.querySelectorAll('.item-row').forEach(r => {
        const q = parseFloat(r.querySelector('.item-qty').value) || 0;
        const p = parseFloat(r.querySelector('.item-price').value) || 0;
        const a = Math.round(q * p * 100) / 100;
        r.querySelector('.item-amount').textContent = a.toFixed(2);
        sub += a;
    });
    const tax = Math.round(sub * (parseFloat(document.getElementById('tax_rate').value) || 0)) / 100;
    document.getElementById('subtotal').textContent = sub.toFixed(2);
    document.getElementById('tax-amt').textContent  = tax.toFixed(2);
    document.getElementById('total').textContent    = (sub + tax).toFixed(2);
}
document.getElementById('add-row').addEventListener('click', () => {
    const r = document.createElement('tr'); r.className = 'item-row';
    r.innerHTML = `<td style="padding:.35rem;"><input type="text" name="items[${rowIndex}][description]" style="width:100%;"></td>
        <td style="padding:.35rem;"><input type="number" name="items[${rowIndex}][quantity]" value="1" min="0" step="0.01" class="item-qty" style="width:100%;text-align:right;" oninput="calcTotals()"></td>
        <td style="padding:.35rem;"><input type="number" name="items[${rowIndex}][unit_price]" value="0" min="0" step="0.01" class="item-price" style="width:100%;text-align:right;" oninput="calcTotals()"></td>
        <td style="padding:.35rem;text-align:right;" class="item-amount">0.00</td>
        <td style="padding:.35rem;"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa-solid fa-xmark"></i></button></td>`;
    document.getElementById('items-body').appendChild(r);
    rowIndex++;
    r.querySelector('.remove-row').addEventListener('click', () => { r.remove(); calcTotals(); });
});
document.querySelectorAll('.remove-row').forEach(b => b.addEventListener('click', () => { b.closest('tr').remove(); calcTotals(); }));
document.querySelectorAll('.item-qty, .item-price').forEach(i => i.addEventListener('input', calcTotals));
calcTotals();
</script>
