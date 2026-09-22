<?php
/**
 * Generic error page. Never renders exception text, file paths or SQL -
 * those go to the error log only. $code and $message are set by the caller,
 * and $message is always one of the app's own error.* strings.
 */
$code    = $code    ?? 500;
$message = $message ?? __('error.generic');
?>
<!DOCTYPE html>
<html lang="<?= \App\Core\Lang::getLocale() === 'de-CH' ? 'de' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (int) $code ?> — ThomasCRM</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--gray-50); }
        .err { text-align: center; padding: 2rem; }
        .err h1 { font-size: 5rem; color: var(--gray-200); line-height: 1; }
        .err h2 { font-size: 1.4rem; margin-bottom: .5rem; }
        .err p  { color: var(--gray-600); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="err">
        <h1><?= (int) $code ?></h1>
        <h2><?= htmlspecialchars($message) ?></h2>
        <p><?= htmlspecialchars(__('error.reference')) ?></p>
        <a href="/" class="btn btn-primary">&larr; <?= htmlspecialchars(__('nav.dashboard')) ?></a>
    </div>
</body>
</html>
