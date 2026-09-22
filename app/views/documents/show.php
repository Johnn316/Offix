<style>
.doc-title-edit {
    outline: none;
    border-bottom: 2px solid transparent;
    padding-bottom: 2px;
    transition: border-color .15s;
    min-width: 200px;
}
.doc-title-edit:hover, .doc-title-edit:focus {
    border-color: var(--brand);
}
.save-badge {
    font-size: .75rem;
    padding: .2rem .55rem;
    border-radius: 99px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.save-saving { background: #fef9c3; color: #854d0e; }
.save-saved  { background: #dcfce7; color: #166534; }
.save-error  { background: #fee2e2; color: #991b1b; }
.editor-iframe-wrap {
    height: calc(100vh - 210px);
    min-height: 450px;
}
.editor-iframe-wrap iframe {
    width: 100%;
    height: 100%;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    display: block;
}
</style>

<div class="page-header" style="align-items:center; flex-wrap:wrap; gap:.75rem;">
    <h1 class="page-title ie-title"
        id="doc-title"
        data-id="<?= $doc['id'] ?>"
        data-original="<?= htmlspecialchars($doc['title']) ?>"
        title="Double-click to edit">
        <?= htmlspecialchars($doc['title']) ?>
    </h1>
    <div style="display:flex; align-items:center; gap:.5rem; margin-left:auto;">
        <span id="save-badge" class="save-badge" style="display:none;"></span>
        <a href="/documents/<?= $doc['id'] ?>/versions" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-clock-rotate-left"></i> <?= __('docs.history') ?>
        </a>
        <a href="/documents" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <?php if ($doc['owner_id'] == $_SESSION['user_id'] || ($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <button type="button" class="btn btn-danger btn-sm"
            data-confirm-delete="true"
            data-action="/documents/<?= $doc['id'] ?>/delete">
            <i class="fa-solid fa-trash"></i>
        </button>
        <form method="POST" action="/documents/<?= $doc['id'] ?>/delete" id="delete-doc-form" style="display:none;"></form>
        <?php endif; ?>
    </div>
</div>

<div style="font-size:.8rem; color:var(--gray-400); margin-bottom:.75rem; display:flex; align-items:center; gap:.5rem;">
    <i class="fa-solid fa-circle" style="font-size:.5rem; color:#22c55e;"></i>
    <span id="online-users"><?= __('docs.only_you') ?></span>
</div>

<div class="editor-iframe-wrap">
    <iframe id="doc-frame"
            src="/documents/<?= $doc['id'] ?>/frame"
            allow="clipboard-read; clipboard-write">
    </iframe>
</div>

<script>
// ── Receive messages from the editor iframe ───────────────────────────────────
window.addEventListener('message', function(e) {
    if (!e.data || !e.data.type) return;

    // ── Relay API calls from iframe (iframe session is unreliable) ────────────
    if (e.data.type === 'api-request') {
        var rid  = e.data.rid;
        var opts = { method: e.data.method || 'GET', headers: {} };
        if (e.data.body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = e.data.body;
        }
        var frame = document.getElementById('doc-frame');
        fetch(e.data.url, opts)
            .then(function(r) {
                if (!r.ok) {
                    return r.text().then(function(t) {
                        throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300));
                    });
                }
                return r.json();
            })
            .then(function(data) {
                if (frame && frame.contentWindow) {
                    frame.contentWindow.postMessage(
                        { type: 'api-response', rid: rid, ok: true, data: data }, '*'
                    );
                }
            })
            .catch(function(err) {
                console.error('[CRM relay error]', e.data.url, err.message);
                if (frame && frame.contentWindow) {
                    frame.contentWindow.postMessage(
                        { type: 'api-response', rid: rid, ok: false, error: err.message }, '*'
                    );
                }
            });
        return;
    }

    if (e.data.type === 'save-status') {
        const badge = document.getElementById('save-badge');
        const map = {
            saving: { text: 'Saving…',     cls: 'save-saving', icon: 'fa-spinner fa-spin' },
            saved:  { text: 'Saved',       cls: 'save-saved',  icon: 'fa-check' },
            error:  { text: 'Save failed', cls: 'save-error',  icon: 'fa-triangle-exclamation' }
        };
        const m = map[e.data.status];
        if (m) {
            badge.style.display = 'inline-flex';
            badge.className = 'save-badge ' + m.cls;
            badge.innerHTML = `<i class="fa-solid ${m.icon}"></i> ${m.text}`;
            if (e.data.status === 'saved') {
                setTimeout(() => { badge.style.display = 'none'; }, 3000);
            }
        }
    }

    if (e.data.type === 'presence') {
        const el = document.getElementById('online-users');
        el.textContent = e.data.names && e.data.names.length
            ? e.data.names.join(', ') + ' <?= __('docs.also_editing') ?>'
            : '<?= __('docs.only_you') ?>';
    }
});

// ── Double-click editable title ───────────────────────────────────────────────
(function() {
    const h1 = document.getElementById('doc-title');
    if (!h1) return;

    h1.addEventListener('dblclick', function() {
        if (h1.dataset.editing) return;
        h1.dataset.editing = '1';
        h1.setAttribute('contenteditable', 'true');
        h1.classList.add('ie-title-active');
        h1.focus();
        const range = document.createRange();
        range.selectNodeContents(h1);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    });

    async function commitTitle() {
        if (!h1.dataset.editing) return;
        delete h1.dataset.editing;
        h1.removeAttribute('contenteditable');
        h1.classList.remove('ie-title-active');
        const title = h1.textContent.trim() || 'Untitled Document';
        h1.textContent = title;
        document.title = title + ' — ThomasCRM';
        try {
            await fetch('/documents/' + h1.dataset.id + '/title', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ title })
            });
        } catch(e) {}
    }

    h1.addEventListener('blur', commitTitle);
    h1.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); h1.blur(); }
        if (e.key === 'Escape') {
            delete h1.dataset.editing;
            h1.removeAttribute('contenteditable');
            h1.classList.remove('ie-title-active');
            h1.textContent = h1.dataset.original;
        }
    });
})();
</script>
