<div class="login-card">
    <h1>ThomasCRM</h1>
    <p>Sign in to your account</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login" novalidate>
<?= csrf_field() ?>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autofocus required
                   placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div style="position:relative;">
                <input type="password" id="password" name="password" required placeholder="••••••••"
                       style="padding-right:2.5rem;">
                <button type="button" onclick="
                    var i=document.getElementById('password');
                    i.type = i.type==='password' ? 'text' : 'password';
                " style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400);">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary login-btn">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
    </form>
</div>
