<?php

declare(strict_types=1);

// Start output buffer early so PHP warnings don't corrupt JSON API responses.
ob_start();


// ── 1. Constants ──────────────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('BASE_URL',  '');

// Set APP_DEBUG=1 in the environment to see errors on screen while developing.
// Off by default so stack traces, file paths and SQL never reach a browser.
define('APP_DEBUG', getenv('APP_DEBUG') === '1');

ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── 2. Session ────────────────────────────────────────────────────────────────
// Cookie flags are set explicitly rather than inherited from php.ini.
// 'secure' tracks the actual scheme: forcing it on would stop the cookie being
// sent over plain HTTP and make local/XAMPP installs impossible to log into.
// It turns itself on as soon as the app is served over HTTPS.
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
    || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── 3. Autoloader ─────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefixMap = [
        'App\\Core\\'                => APP_PATH . '/core/',
        'App\\Controllers\\'         => APP_PATH . '/controllers/',
        'App\\Models\\'              => APP_PATH . '/models/',
        'App\\Modules\\Invoicing\\'  => ROOT_PATH . '/app/modules/invoicing/',
        'App\\Modules\\ItPlanning\\' => ROOT_PATH . '/app/modules/it_planning/',
    ];
    foreach ($prefixMap as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) require $file;
            return;
        }
    }
});

// ── 4. Helpers + Lang ─────────────────────────────────────────────────────────
require APP_PATH . '/core/helpers.php';
\App\Core\Lang::boot();

// ── 4b. Last-resort error handling ────────────────────────────────────────────
// Anything that escapes a controller (a failed query, a bad call) would
// otherwise render PHP's own trace, which names files, lines and SQL.
set_exception_handler(function (\Throwable $e): void {
    error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());
    render_error_page(500);
});

register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err === null || !in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    error_log('Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
    render_error_page(500);
});

// ── 5. Auth guard ─────────────────────────────────────────────────────────────
$uri = '/' . trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
$publicPaths = ['/login', '/logout'];
$isPublic    = in_array($uri, $publicPaths, true) || str_starts_with($uri, '/file/');

if (!$isPublic && empty($_SESSION['user_id'])) {
    if (str_starts_with($uri, '/api/')) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header('Location: /login');
    exit;
}

// ── 6. CSRF guard ─────────────────────────────────────────────────────────────
// Every state-changing request must carry the session token, supplied as a
// _token form field, an X-CSRF-Token header, or a _token key in a JSON body.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
    if (str_starts_with($uri, '/api/') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json');
        http_response_code(419);
        echo json_encode(['error' => 'Invalid or missing CSRF token']);
        exit;
    }

    http_response_code(419);
    $_SESSION['flash_error'] = __('error.session_expired');
    header('Location: ' . (empty($_SESSION['user_id']) ? '/login' : '/'));
    exit;
}

// ── 7. Boot router ────────────────────────────────────────────────────────────
use App\Core\Router;
$router = new Router();
require ROOT_PATH . '/routes/web.php';

// ── 8. Dispatch ───────────────────────────────────────────────────────────────
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
