<div class="page-header">
    <h1 class="page-title"><?= __('settings.title') ?></h1>
</div>

<form method="POST" action="/settings/update">
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; align-items:start;">

    <!-- Currency selector -->
    <div class="card">
        <div class="card-header">
            <h2><?= __('settings.currencies') ?></h2>
        </div>
        <div class="card-body">

            <p style="font-size:.85rem;color:var(--gray-500);margin-bottom:1.1rem;">
                <?= __('settings.currencies_hint') ?>
            </p>

            <!-- Search filter -->
            <div style="position:relative; margin-bottom:.85rem;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.85rem;"></i>
                <input type="text" id="currency-search" placeholder="<?= __('settings.search_currencies') ?>"
                       style="padding-left:2.2rem;" oninput="filterCurrencies(this.value)">
            </div>

            <!-- Currency checkbox grid -->
            <div id="currency-list" style="max-height:380px; overflow-y:auto; border:1px solid var(--gray-200); border-radius:var(--radius); padding:.5rem;">
                <?php foreach ($allCurrencies as $code => [$name, $symbol]): ?>
                <label class="currency-row" data-search="<?= strtolower($code . ' ' . $name) ?>">
                    <input type="checkbox" name="currencies[]" value="<?= $code ?>"
                           <?= in_array($code, $activeCurrencies, true) ? 'checked' : '' ?>
                           onchange="syncDefault()">
                    <span class="currency-code"><?= $code ?></span>
                    <span class="currency-name"><?= htmlspecialchars($name) ?></span>
                    <span class="currency-symbol"><?= htmlspecialchars($symbol) ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:.6rem; font-size:.78rem; color:var(--gray-400);">
                <span id="selected-count"><?= count($activeCurrencies) ?></span> <?= __('settings.selected') ?>
            </div>
        </div>
    </div>

    <!-- Default currency + save -->
    <div style="display:flex; flex-direction:column; gap:1.25rem;">

        <div class="card">
            <div class="card-header"><h2><?= __('settings.default_currency') ?></h2></div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--gray-500);margin-bottom:1rem;">
                    <?= __('settings.default_currency_hint') ?>
                </p>
                <div class="form-group" style="margin-bottom:0;">
                    <label><?= __('settings.default_currency') ?></label>
                    <select name="default_currency" id="default-currency-select" style="max-width:220px;">
                        <?php foreach ($activeCurrencies as $code): ?>
                            <?php $info = $allCurrencies[$code] ?? [$code, ''] ?>
                            <option value="<?= $code ?>" <?= $defaultCurrency === $code ? 'selected' : '' ?>>
                                <?= $code ?> — <?= htmlspecialchars($info[0]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-floppy-disk"></i> <?= __('settings.save') ?>
                </button>
            </div>
        </div>

    </div>
</div>
</form>

<style>
.currency-row {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .45rem .6rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background .1s;
    user-select: none;
}
.currency-row:hover { background: var(--gray-50); }
.currency-row input[type="checkbox"] { accent-color: var(--brand); width:15px; height:15px; flex-shrink:0; }
.currency-code { font-weight: 600; font-size: .82rem; color: var(--gray-800); min-width: 42px; }
.currency-name { font-size: .82rem; color: var(--gray-500); flex:1; }
.currency-symbol { font-size: .82rem; color: var(--gray-400); min-width: 28px; text-align:right; }
</style>

<script>
function filterCurrencies(q) {
    q = q.toLowerCase().trim();
    let count = 0;
    document.querySelectorAll('.currency-row').forEach(row => {
        const match = !q || row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        if (match && row.querySelector('input').checked) count++;
    });
}

function syncDefault() {
    const sel = document.getElementById('default-currency-select');
    const prev = sel.value;
    const checked = [...document.querySelectorAll('input[name="currencies[]"]:checked')]
        .map(cb => cb.value);

    // Rebuild default select options
    const allCurrencies = <?= json_encode(array_map(fn($v) => $v[0], $allCurrencies)) ?>;
    sel.innerHTML = checked.map(code =>
        `<option value="${code}" ${code === prev ? 'selected' : ''}>${code} — ${allCurrencies[code] ?? code}</option>`
    ).join('');

    // Update selected count
    document.getElementById('selected-count').textContent = checked.length;
}
</script>
