<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <title>ThomasCRM — Login</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--gray-50); }
        .login-box { width:100%; max-width:400px; padding:1rem; }
        .login-logo { text-align:center; margin-bottom:1.5rem; }
        .login-logo img { max-height:64px; border-radius:6px; }
        .login-card { background:#fff; border:1px solid var(--gray-200); border-radius:var(--radius); padding:2rem; box-shadow:0 4px 24px rgba(0,0,0,.07); }
        .login-card h1 { font-size:1.3rem; font-weight:700; margin-bottom:.25rem; text-align:center; }
        .login-card p  { color:var(--gray-600); font-size:.875rem; text-align:center; margin-bottom:1.5rem; }
        .login-card .form-group { margin-bottom:1rem; }
        .login-btn { width:100%; padding:.65rem; font-size:.95rem; margin-top:.5rem; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <img src="/assets/images/logo.jpeg" alt="ThomasCRM">
    </div>
    <?= $content ?>
</div>
</body>
</html>
