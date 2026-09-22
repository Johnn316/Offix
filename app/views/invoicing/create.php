<?php
function err(string $f, array $e): string {
    return isset($e[$f]) ? '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($e[$f]) . '</span>' : '';
}
function ov(string $f, array $old, string $def = ''): string {
    return htmlspecialchars($old[$f] ?? $def);
}
?>
<div class="page-header">
    <h1 class="page-title">New Invoice</h1>
    <a href="/invoicing" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="/invoicing/store" novalidate>
<?= csrf_field() ?>
<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; align-items:start;">

    <!-- Left: Line Items -->
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
                    <?php foreach ($oldItems as $i => $item): ?>
                    <tr class="item-row">
                        <td style="padding:.35rem;"><input type="text" name="items[<?= $i ?>][description]" value="<?= htmlspecialchars($item['description']) ?>" placeholder="Description" style="width:100%;"></td>
                        <td style="padding:.35rem;"><input type="number" name="items[<?= $i ?>][quantity]" value="<?= htmlspecialchars($item['quantity'] ?? 1) ?>" min="0" step="0.01" class="item-qty" style="width:100%; text-align:right;"></td>
                        <td style="padding:.35rem;"><input type="number" name="items[<?= $i ?>][unit_price]" value="<?= htmlspecialchars($item['unit_price'] ?? 0) ?>" min="0" step="0.01" class="item-price" style="width:100%; text-align:right;"></td>
                        <td style="padding:.35rem; text-align:right;" class="item-amount"><?= number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) ?></td>
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
                    <div style="display:flex; gap:2rem;">
                        <span style="color:var(--gray-600); font-size:.9rem;">Subtotal</span>
                        <span id="subtotal" style="min-width:80px; text-align:right;">0.00</span>
                    </div>
                    <div style="display:flex; gap:2rem; align-items:center;">
                        <span style="color:var(--gray-600); font-size:.9rem;">Tax %</span>
                        <input type="number" name="tax_rate" id="tax_rate" value="<?= ov('tax_rate', $old, '0') ?>" min="0" max="100" step="0.1" style="width:70px; text-align:right;" oninput="calcTotals()">
                    </div>
                    <div style="display:flex; gap:2rem;">
                        <span style="color:var(--gray-600); font-size:.9rem;">Tax</span>
                        <span id="tax-amt" style="min-width:80px; text-align:right;">0.00</span>
                    </div>
                    <div style="display:flex; gap:2rem; font-weight:700; font-size:1.05rem; border-top:1px solid var(--gray-200); padding-top:.5rem;">
                        <span>Total</span>
                        <span id="total" style="min-width:80px; text-align:right;">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Invoice Details -->
    <div class="card">
        <div class="card-header"><h2>Details</h2></div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Invoice #</label>
                <input type="text" name="number" value="<?= ov('number', $old, $number) ?>" required>
                <?= err('number', $errors) ?>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Status</label>
                <select name="status">
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= ov('status', $old, 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Client</label>
                <select name="contact_id">
                    <option value="">— None —</option>
                    <?php foreach ($contacts as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ov('contact_id', $old) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Project</label>
                <select name="project_id">
                    <option value="">— None —</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ov('project_id', $old) == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Issue Date <span style="color:var(--danger)">*</span></label>
                <input type="date" name="issue_date" value="<?= ov('issue_date', $old, date('Y-m-d')) ?>" required>
                <?= err('issue_date', $errors) ?>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= ov('due_date', $old) ?>">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= ov('notes', $old) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                <i class="fa-solid fa-floppy-disk"></i> Save Invoice
            </button>
        </div>
    </div>

</div>
</form>

<script>
let rowIndex = <?= count($oldItems) ?>;

function calcTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const amt   = Math.round(qty * price * 100) / 100;
        row.querySelector('.item-amount').textContent = amt.toFixed(2);
        subtotal += amt;
    });
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const tax     = Math.round(subtotal * taxRate) / 100;
    const total   = subtotal + tax;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax-amt').textContent  = tax.toFixed(2);
    document.getElementById('total').textContent    = total.toFixed(2);
}

document.getElementById('add-row').addEventListener('click', () => {
    const tbody = document.getElementById('items-body');
    const row   = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td style="padding:.35rem;"><input type="text" name="items[${rowIndex}][description]" placeholder="Description" style="width:100%;"></td>
        <td style="padding:.35rem;"><input type="number" name="items[${rowIndex}][quantity]" value="1" min="0" step="0.01" class="item-qty" style="width:100%;text-align:right;" oninput="calcTotals()"></td>
        <td style="padding:.35rem;"><input type="number" name="items[${rowIndex}][unit_price]" value="0" min="0" step="0.01" class="item-price" style="width:100%;text-align:right;" oninput="calcTotals()"></td>
        <td style="padding:.35rem;text-align:right;" class="item-amount">0.00</td>
        <td style="padding:.35rem;"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa-solid fa-xmark"></i></button></td>`;
    tbody.appendChild(row);
    rowIndex++;
    bindRemove(row);
});

function bindRemove(row) {
    row.querySelector('.remove-row').addEventListener('click', () => {
        row.remove(); calcTotals();
    });
}

document.querySelectorAll('.remove-row').forEach(btn => bindRemove(btn.closest('tr')));
document.querySelectorAll('.item-qty, .item-price').forEach(inp => inp.addEventListener('input', calcTotals));
calcTotals();
</script>
