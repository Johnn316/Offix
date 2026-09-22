<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Document Editor') ?></title>
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #fff;
    overflow: hidden;
}
.frame-wrap {
    display: flex;
    flex-direction: column;
    height: 100vh;
}
/* ── Status bar ── */
.frame-status {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .3rem .75rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: .75rem;
    color: #64748b;
    flex-shrink: 0;
}
.frame-status .presence {
    flex: 1;
    display: flex;
    align-items: center;
    gap: .35rem;
}
/* ── Save status ── */
.save-status { font-size: .72rem; font-weight: 600; }
.save-saving { color: #b45309; }
.save-saved  { color: #16a34a; }
.save-error  { color: #dc2626; }
/* ── Quill overrides for full-height ── */
.ql-toolbar {
    border-left: none !important;
    border-right: none !important;
    border-top: none !important;
    border-bottom: 1px solid #e2e8f0 !important;
    background: #fff;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 10;
}
.ql-container {
    border: none !important;
    flex: 1;
    overflow-y: auto;
    font-size: 1rem;
    font-family: 'Segoe UI', Georgia, serif;
}
.ql-editor {
    min-height: 100%;
    padding: 2rem 3rem;
    max-width: 860px;
    margin: 0 auto;
    line-height: 1.7;
}
.ql-editor.ql-blank::before {
    color: #cbd5e1;
    font-style: normal;
}
/* ── Peer cursor colours (up to 6 users) ── */
.peer-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
}
</style>
</head>
<body>
<?= $content ?>
</body>
</html>
