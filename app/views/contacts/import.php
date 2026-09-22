<div class="page-header">
    <h1 class="page-title"><?= __('contacts.import') ?></h1>
    <a href="/contacts" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('action.back') ?></a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; align-items:start;">

    <!-- Upload form -->
    <div class="card">
        <div class="card-header"><h2><?= __('import.upload_file') ?></h2></div>
        <div class="card-body">
            <form method="POST" action="/contacts/import/run" enctype="multipart/form-data" novalidate>

                <div id="drop-zone" onclick="document.getElementById('import-file').click();">
                    <i class="fa-solid fa-file-arrow-up" style="font-size:2rem; color:var(--gray-300); margin-bottom:.6rem;"></i>
                    <p style="margin:0; font-weight:500; color:var(--gray-600);"><?= __('import.drag_drop') ?></p>
                    <p style="margin:.25rem 0 0; font-size:.8rem; color:var(--gray-400);">CSV · VCF</p>
                </div>

                <input type="file" id="import-file" name="import_file" accept=".csv,.vcf"
                       style="display:none;" onchange="showSelected(this)">

                <div id="file-selected" style="display:none; margin-top:.85rem; padding:.65rem .9rem;
                     background:var(--gray-50); border:1px solid var(--gray-200); border-radius:var(--radius);
                     display:none; align-items:center; gap:.6rem;">
                    <i class="fa-solid fa-file-lines" style="color:var(--brand);"></i>
                    <span id="file-name" style="font-size:.875rem; font-weight:500;"></span>
                    <button type="button" onclick="clearFile()" style="margin-left:auto; background:none; border:none; cursor:pointer; color:var(--gray-400);">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1.1rem;">
                    <i class="fa-solid fa-file-import"></i> <?= __('import.run') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Instructions -->
    <div style="display:flex; flex-direction:column; gap:1.25rem;">

        <div class="card">
            <div class="card-header"><h2><i class="fa-brands fa-google" style="color:#4285F4;"></i> <?= __('import.google_csv_title') ?></h2></div>
            <div class="card-body">
                <ol style="margin:0; padding-left:1.25rem; font-size:.875rem; color:var(--gray-600); line-height:1.9;">
                    <li><?= __('import.google_csv_1') ?></li>
                    <li><?= __('import.google_csv_2') ?></li>
                    <li><?= __('import.google_csv_3') ?></li>
                    <li><?= __('import.google_csv_4') ?></li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2><i class="fa-solid fa-id-card" style="color:var(--gray-500);"></i> <?= __('import.vcf_title') ?></h2></div>
            <div class="card-body">
                <ol style="margin:0; padding-left:1.25rem; font-size:.875rem; color:var(--gray-600); line-height:1.9;">
                    <li><?= __('import.vcf_1') ?></li>
                    <li><?= __('import.vcf_2') ?></li>
                    <li><?= __('import.vcf_3') ?></li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2><?= __('import.notes_title') ?></h2></div>
            <div class="card-body">
                <ul style="margin:0; padding-left:1.25rem; font-size:.875rem; color:var(--gray-600); line-height:1.9;">
                    <li><?= __('import.note_duplicate') ?></li>
                    <li><?= __('import.note_required') ?></li>
                    <li><?= __('import.note_fields') ?></li>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
#drop-zone {
    border: 2px dashed var(--gray-200);
    border-radius: var(--radius);
    padding: 2.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    display: flex;
    flex-direction: column;
    align-items: center;
}
#drop-zone:hover, #drop-zone.drag-over {
    border-color: var(--brand);
    background: var(--brand-light);
}
</style>

<script>
function showSelected(input) {
    if (!input.files || !input.files[0]) return;
    document.getElementById('file-name').textContent = input.files[0].name;
    document.getElementById('file-selected').style.display = 'flex';
}

function clearFile() {
    document.getElementById('import-file').value = '';
    document.getElementById('file-selected').style.display = 'none';
}

// Drag-and-drop
const zone = document.getElementById('drop-zone');
zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const inp = document.getElementById('import-file');
    const dt  = new DataTransfer();
    dt.items.add(file);
    inp.files = dt.files;
    showSelected(inp);
});
</script>
