<!DOCTYPE html>
<html lang="<?= \App\Core\Lang::getLocale() === 'de-CH' ? 'de' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'ThomasCRM') ?> — ThomasCRM</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:99;"
     onclick="this.style.display='none'; document.querySelector('.sidebar').classList.remove('open');"></div>

<!-- ── Sidebar ─────────────────────────────────────────────────────────── -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="/assets/images/logo.jpeg" alt="Logo" class="sidebar-logo">
    </div>
    <nav class="sidebar-nav">
        <a href="/"><i class="fa-solid fa-gauge-high nav-icon"></i> <?= __('nav.dashboard') ?></a>

        <div class="sidebar-section"><?= __('nav.core') ?></div>
        <a href="/contacts"><i class="fa-solid fa-address-book nav-icon"></i> <?= __('nav.contacts') ?></a>
        <a href="/tasks"><i class="fa-solid fa-list-check nav-icon"></i> <?= __('nav.tasks') ?></a>
        <a href="/tasks/planner"><i class="fa-solid fa-calendar-days nav-icon"></i> <?= __('nav.task_planner') ?></a>
        <a href="/projects"><i class="fa-solid fa-briefcase nav-icon"></i> <?= __('nav.projects') ?></a>
        <a href="/documents"><i class="fa-solid fa-file-lines nav-icon"></i> <?= __('nav.documents') ?></a>

        <?php
        $enabledModules = \App\Core\ModuleManager::enabledModules();
        if (!empty($enabledModules)):
        ?>
        <div class="sidebar-section"><?= __('nav.modules') ?></div>
        <?php foreach ($enabledModules as $mod): ?>
        <a href="/<?= htmlspecialchars($mod['name']) ?>">
            <i class="fa-solid <?= htmlspecialchars($mod['icon']) ?> nav-icon"></i>
            <?= htmlspecialchars($mod['label']) ?>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <div class="sidebar-section"><?= __('nav.settings') ?></div>
        <a href="/users"><i class="fa-solid fa-users nav-icon"></i> <?= __('nav.users') ?></a>
        <a href="/settings"><i class="fa-solid fa-sliders nav-icon"></i> <?= __('nav.settings_general') ?></a>
        <a href="/settings/modules"><i class="fa-solid fa-puzzle-piece nav-icon"></i> <?= __('nav.modules') ?></a>
        <?php endif; ?>
    </nav>
</aside>

<!-- ── Main wrapper ────────────────────────────────────────────────────── -->
<div class="main-wrapper">

    <header class="topbar">
        <button class="topbar-hamburger" onclick="
            document.querySelector('.sidebar').classList.toggle('open');
            document.getElementById('sidebar-overlay').style.display = document.querySelector('.sidebar').classList.contains('open') ? 'block' : 'none';
        "><i class="fa-solid fa-bars"></i></button>

        <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>

        <!-- User avatar + dropdown -->
        <div class="user-menu" id="user-menu">
            <button class="user-menu-btn" onclick="document.getElementById('user-dropdown').classList.toggle('open');">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="/file/avatars/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                         class="topbar-avatar" alt="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                <?php else: ?>
                    <div class="topbar-avatar-initials">
                        <?= strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <span class="topbar-username"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:.7rem;color:var(--gray-400);"></i>
            </button>

            <div class="user-dropdown" id="user-dropdown">
                <div class="user-dropdown-header">
                    <div style="font-weight:600; font-size:.875rem;"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                    <div style="font-size:.75rem; color:var(--gray-400); text-transform:capitalize;"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></div>
                </div>
                <a href="/profile" class="user-dropdown-item">
                    <i class="fa-solid fa-circle-user"></i> <?= __('profile.settings') ?>
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="/logout" class="user-dropdown-item user-dropdown-item-danger">
                    <i class="fa-solid fa-right-from-bracket"></i> <?= __('auth.logout') ?>
                </a>
            </div>
        </div>
    </header>

    <div style="padding:0 2rem; margin-top:1rem;">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>

    <main class="content"><?= $content ?></main>
</div>

<!-- Delete modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;color:var(--danger);margin-bottom:.75rem;"></i>
        <h3><?= __('delete.confirm_title') ?></h3>
        <p><?= __('delete.confirm_body') ?></p>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="cancel-delete"><?= __('action.cancel') ?></button>
            <form id="delete-form" method="POST" style="display:inline;">
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i> <?= __('delete.yes') ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="/js/app.js"></script>
<script>
// Close user dropdown when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('user-menu');
    if (menu && !menu.contains(e.target)) {
        document.getElementById('user-dropdown').classList.remove('open');
    }
});
</script>
</body>
</html>
