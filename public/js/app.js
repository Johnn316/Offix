/**
 * ThomasCRM — minimal vanilla JS
 * No dependencies, no build step.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Mobile sidebar toggle ──────────────────────────────────────────────
    const hamburger = document.querySelector('.topbar-hamburger');
    const sidebar   = document.querySelector('.sidebar');
    const overlay   = document.getElementById('sidebar-overlay');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // ── Delete confirmation modal ──────────────────────────────────────────
    // Buttons with data-confirm-delete="true" open the modal.
    // The modal's form action is updated to the button's data-action.
    const deleteModal  = document.getElementById('delete-modal');
    const deleteForm   = document.getElementById('delete-form');
    const cancelDelete = document.getElementById('cancel-delete');

    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (deleteModal && deleteForm) {
                deleteForm.action = btn.dataset.action;
                deleteModal.classList.add('open');
            }
        });
    });

    if (cancelDelete && deleteModal) {
        cancelDelete.addEventListener('click', () => {
            deleteModal.classList.remove('open');
        });
    }

    // Close modal on overlay click
    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('open');
            }
        });
    }

    // ── Auto-dismiss flash messages after 4 s ─────────────────────────────
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

    // ── Mark active nav link ───────────────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '/' && currentPath.startsWith(href)) {
            link.classList.add('active');
        } else if (href === '/' && currentPath === '/') {
            link.classList.add('active');
        }
    });

    // ── Inline field editing (double-click .ie-field) ─────────────────────
    document.addEventListener('dblclick', function(e) {
        const el = e.target.closest('.ie-field');
        if (el && !el.dataset.editing) activateInlineEdit(el);
    });

    // ── Inline title editing (double-click .ie-title h1) ──────────────────
    document.querySelectorAll('h1.ie-title').forEach(function(h1) {
        h1.title = 'Double-click to edit';
        h1.addEventListener('dblclick', function() {
            if (h1.dataset.editing) return;
            h1.dataset.editing = '1';
            h1.setAttribute('contenteditable', 'true');
            h1.classList.add('ie-title-active');
            h1.focus();
            // move cursor to end
            const range = document.createRange();
            range.selectNodeContents(h1);
            range.collapse(false);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        });
        h1.addEventListener('blur', function() {
            if (!h1.dataset.editing) return;
            delete h1.dataset.editing;
            h1.removeAttribute('contenteditable');
            h1.classList.remove('ie-title-active');
            const newVal = h1.textContent.trim() || h1.dataset.original;
            h1.textContent = newVal;
            saveInlineField(h1.dataset.model, h1.dataset.id, h1.dataset.field, newVal);
        });
        h1.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); h1.blur(); }
            if (e.key === 'Escape') {
                delete h1.dataset.editing;
                h1.removeAttribute('contenteditable');
                h1.classList.remove('ie-title-active');
                h1.textContent = h1.dataset.original;
            }
        });
    });
});

/** CSRF token issued for this session, rendered into the page <head>. */
function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

async function saveInlineField(model, id, field, value) {
    try {
        await fetch('/api/inline-edit', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken()
            },
            body:    JSON.stringify({ model, id, field, value })
        });
    } catch(e) {}
}

function activateInlineEdit(el) {
    el.dataset.editing = '1';

    const model    = el.dataset.model;
    const id       = el.dataset.id;
    const field    = el.dataset.field;
    const type     = el.dataset.type || 'text';
    const original = el.dataset.value !== undefined ? el.dataset.value : el.textContent.trim();
    const display  = original === '' ? '—' : original;

    let input;
    if (type === 'textarea') {
        input = document.createElement('textarea');
        input.rows = 3;
        input.value = original;
    } else {
        input = document.createElement('input');
        input.type = type;
        input.value = original;
    }
    input.className = 'ie-input';

    el.innerHTML = '';
    el.appendChild(input);
    input.focus();
    if (type === 'text' || type === 'email') {
        input.select();
    }

    async function commit() {
        delete el.dataset.editing;
        const newVal = input.value.trim();

        if (type === 'email' && newVal) {
            el.innerHTML = '<a href="mailto:' + newVal + '">' + newVal + '</a>';
            el.dataset.value = newVal;
        } else if (type === 'date' && newVal) {
            el.dataset.value = newVal;
            // Format date for display: YYYY-MM-DD → Mon D, YYYY
            const d = new Date(newVal + 'T00:00:00');
            el.textContent = d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        } else {
            el.textContent = newVal || '—';
            if (el.dataset.value !== undefined) el.dataset.value = newVal;
        }

        // keep page h1 in sync if editing name/title
        if (newVal && (field === 'name' || field === 'title')) {
            const h1 = document.querySelector('h1.ie-title');
            if (h1) { h1.textContent = newVal; h1.dataset.original = newVal; }
        }

        await saveInlineField(model, id, field, newVal);
    }

    function cancel() {
        delete el.dataset.editing;
        if (type === 'email' && original) {
            el.innerHTML = '<a href="mailto:' + original + '">' + original + '</a>';
        } else {
            el.textContent = display;
        }
    }

    input.addEventListener('blur', commit);
    input.addEventListener('keydown', function(e) {
        if (type !== 'textarea' && e.key === 'Enter') {
            e.preventDefault(); input.removeEventListener('blur', commit); commit();
        }
        if (e.key === 'Escape') {
            input.removeEventListener('blur', commit); cancel();
        }
    });
}
