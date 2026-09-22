<?php
$docJson = json_encode([
    'id'       => $doc['id'],
    'userId'   => $userId,
    'userName' => $userName,
    'content'  => $doc['content'],
]);
?>

<div class="frame-wrap">

    <!-- Status bar -->
    <div class="frame-status">
        <div class="presence">
            <i class="fa-solid fa-circle" style="font-size:.45rem; color:#22c55e;"></i>
            <span id="presence"></span>
        </div>
        <div style="display:flex; align-items:center; gap:.5rem;">
            <button id="btn-save-now" style="padding:.25rem .65rem; font-size:.75rem; cursor:pointer; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#374151; display:flex; align-items:center; gap:.3rem;">
                <i class="fa-solid fa-floppy-disk"></i> Save
            </button>
            <span id="save-status" class="save-status"></span>
        </div>
    </div>

    <!-- Toolbar -->
    <div id="toolbar">
        <select class="ql-header"><option value="1"></option><option value="2"></option><option value="3"></option><option selected></option></select>
        <button class="ql-bold"></button>
        <button class="ql-italic"></button>
        <button class="ql-underline"></button>
        <button class="ql-strike"></button>
        <select class="ql-size"><option value="small"></option><option selected></option><option value="large"></option><option value="huge"></option></select>
        <select class="ql-color"></select>
        <select class="ql-background"></select>
        <button class="ql-list" value="ordered"></button>
        <button class="ql-list" value="bullet"></button>
        <button class="ql-indent" value="-1"></button>
        <button class="ql-indent" value="+1"></button>
        <select class="ql-align"></select>
        <button class="ql-link"></button>
        <button class="ql-clean"></button>
    </div>

    <!-- Editor area -->
    <div id="editor"></div>

</div>

<script>
(function () {
'use strict';

const DOC = <?= $docJson ?>;

// ── 1. API proxy via parent page (avoids iframe session issues) ───────────────
// All HTTP calls are relayed through show.php which holds the reliable session.
const _pending = {};

function apiProxy(url, method, bodyObj) {
    return new Promise(function(resolve, reject) {
        const rid   = Math.random().toString(36).slice(2);
        const timer = setTimeout(function() {
            delete _pending[rid];
            reject(new Error('Timeout'));
        }, 10000);
        _pending[rid] = { resolve: resolve, reject: reject, timer: timer };
        window.parent.postMessage({
            type:   'api-request',
            rid:    rid,
            url:    url,
            method: method || 'GET',
            body:   bodyObj ? JSON.stringify(bodyObj) : null
        }, '*');
    });
}

// Listen for responses from parent
window.addEventListener('message', function(e) {
    if (!e.data || e.data.type !== 'api-response') return;
    var p = _pending[e.data.rid];
    if (!p) return;
    clearTimeout(p.timer);
    delete _pending[e.data.rid];
    if (e.data.ok) p.resolve(e.data.data);
    else p.reject(new Error(e.data.error || 'error'));
});

// ── 2. Inline-style attributors ───────────────────────────────────────────────
const AlignStyle      = Quill.import('attributors/style/align');
const BackgroundStyle = Quill.import('attributors/style/background');
const ColorStyle      = Quill.import('attributors/style/color');
const FontStyle       = Quill.import('attributors/style/font');
const SizeStyle       = Quill.import('attributors/style/size');
Quill.register(AlignStyle,      true);
Quill.register(BackgroundStyle, true);
Quill.register(ColorStyle,      true);
Quill.register(FontStyle,       true);
Quill.register(SizeStyle,       true);

// ── 3. Initialize Quill ───────────────────────────────────────────────────────
const quill = new Quill('#editor', {
    theme: 'snow',
    modules: { toolbar: '#toolbar' },
    placeholder: 'Start writing…'
});

let lastContent = null;

if (DOC.content) {
    try {
        quill.setContents(JSON.parse(DOC.content), 'api');
        lastContent = DOC.content;
    } catch(e) {
        quill.root.innerHTML = DOC.content;
    }
}

// ── 4. Save status display ────────────────────────────────────────────────────
function setSaveStatus(status, detail) {
    var el = document.getElementById('save-status');
    if (!el) return;
    var cfg = {
        saving: { text: 'Saving…', cls: 'save-saving' },
        saved:  { text: '✓ Saved', cls: 'save-saved'  },
        error:  { text: '⚠ Save failed', cls: 'save-error' }
    };
    var c = cfg[status];
    if (c) {
        el.textContent = c.text + (detail ? ': ' + detail.substring(0, 80) : '');
        el.className = 'save-status ' + c.cls;
        el.title = detail || '';
    }
    window.parent.postMessage({ type: 'save-status', status: status }, '*');
}

// ── 5. Save content ───────────────────────────────────────────────────────────
var isSaving  = false;
var lastTs    = Math.floor(Date.now() / 1000);
var savedAt   = 0;
var saveTimer = null;

async function saveContent() {
    if (isSaving) return;
    isSaving = true;
    setSaveStatus('saving');
    var content = JSON.stringify(quill.getContents());
    try {
        var data = await apiProxy('/api/snapshot', 'POST', { doc_id: DOC.id, content: content });
        if (data && data.ok === false) {
            setSaveStatus('error', data.error || 'Server error');
        } else {
            lastContent = content;
            lastTs  = data.ts || Math.floor(Date.now() / 1000);
            savedAt = Date.now();
            setSaveStatus('saved');
        }
    } catch(e) {
        setSaveStatus('error', e.message);
    }
    isSaving = false;
}

// ── 6. Poll for remote changes ────────────────────────────────────────────────
async function pollChanges() {
    if (isSaving) return;
    if (Date.now() - savedAt < 4000) return; // grace period after own save
    try {
        var data = await apiProxy(
            '/api/doc/state?id=' + encodeURIComponent(DOC.id) + '&ts=' + lastTs,
            'GET'
        );

        if (data.changed && data.content && data.content !== lastContent) {
            lastTs      = data.ts;
            lastContent = data.content;
            var sel = quill.getSelection();
            try { quill.setContents(JSON.parse(data.content), 'api'); } catch(e) {}
            if (sel) {
                var len = quill.getLength();
                quill.setSelection(Math.min(sel.index, len - 1), 0, 'api');
            }
        } else if (data.ts) {
            lastTs = Math.max(lastTs, data.ts);
        }

        if (Array.isArray(data.editors)) {
            var others = data.editors
                .filter(function(e) { return String(e.id) !== String(DOC.userId); })
                .map(function(e) { return e.name; });
            var el = document.getElementById('presence');
            if (el) el.textContent = others.length ? others.join(', ') + ' also editing' : '';
            window.parent.postMessage({ type: 'presence', names: others }, '*');
        }
    } catch(e) {}
}

// ── 7. Heartbeat ──────────────────────────────────────────────────────────────
async function sendHeartbeat() {
    try {
        await apiProxy('/api/doc/heartbeat', 'POST', { doc_id: DOC.id, user_name: DOC.userName });
    } catch(e) {}
}

// ── 8. Debounce save on typing ────────────────────────────────────────────────
quill.on('text-change', function(_delta, _old, source) {
    if (source !== 'user') return;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveContent, 800);
});

// ── 9. Save button ────────────────────────────────────────────────────────────
document.getElementById('btn-save-now')?.addEventListener('click', saveContent);

// ── 10. Timers ────────────────────────────────────────────────────────────────
setInterval(pollChanges,   2000);
setInterval(sendHeartbeat, 5000);
setInterval(saveContent,  60000);

sendHeartbeat();

// ── 11. Best-effort save on tab close ─────────────────────────────────────────
window.addEventListener('beforeunload', function() {
    try {
        // sendBeacon cannot set headers, so the CSRF token travels in the body.
        var tokenEl = document.querySelector('meta[name="csrf-token"]');
        navigator.sendBeacon('/api/snapshot', new Blob(
            [JSON.stringify({
                doc_id:  DOC.id,
                content: JSON.stringify(quill.getContents()),
                _token:  tokenEl ? tokenEl.getAttribute('content') : ''
            })],
            { type: 'application/json' }
        ));
    } catch(e) {}
});

})();
</script>
