<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'Print') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; font-size:13px; color:#222; background:#fff; padding:30px; }
        .print-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; }
        .print-logo img { max-height:60px; }
        .print-title h1 { font-size:2rem; color:#1e293b; }
        .print-title p { color:#64748b; font-size:.85rem; }
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem; }
        .meta-block label { display:block; font-size:.7rem; text-transform:uppercase; color:#64748b; margin-bottom:.15rem; letter-spacing:.05em; }
        .meta-block p { font-weight:500; }
        table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
        th { background:#f1f5f9; text-align:left; padding:.5rem .75rem; font-size:.75rem; text-transform:uppercase; color:#475569; }
        td { padding:.5rem .75rem; border-bottom:1px solid #f1f5f9; }
        td.right, th.right { text-align:right; }
        .totals-block { display:flex; justify-content:flex-end; }
        .totals-inner { min-width:250px; }
        .totals-inner div { display:flex; justify-content:space-between; padding:.25rem 0; }
        .totals-inner .grand { font-weight:700; font-size:1.1rem; border-top:2px solid #1e293b; padding-top:.5rem; margin-top:.25rem; }
        .notes { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:.75rem 1rem; margin-top:1.5rem; }
        .notes label { font-size:.75rem; text-transform:uppercase; color:#64748b; display:block; margin-bottom:.35rem; }
        .footer { margin-top:2rem; text-align:center; color:#94a3b8; font-size:.75rem; }
        .badge { display:inline-block; padding:.15rem .6rem; border-radius:20px; font-size:.7rem; font-weight:600; text-transform:uppercase; background:#dbeafe; color:#1d4ed8; }
        .badge-paid { background:#dcfce7; color:#166534; }
        .badge-sent { background:#fef9c3; color:#854d0e; }
        .badge-overdue { background:#fee2e2; color:#991b1b; }
        .badge-cancelled { background:#f1f5f9; color:#475569; }
        @media print { @page { margin:20mm; } .no-print { display:none; } }
    </style>
</head>
<body>
<?= $content ?>
<script>
document.addEventListener('DOMContentLoaded', () => window.print());
</script>
</body>
</html>
