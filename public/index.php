<?php

declare(strict_types=1);

// Start output buffer early so PHP warnings don't corrupt JSON API responses.
ob_start();


// ── 1. Constants ──────────────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('BASE_URL',  '');

// ── 2. Session ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

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

// ── 6. Boot router ────────────────────────────────────────────────────────────
use App\Core\Router;
$router = new Router();
require ROOT_PATH . '/routes/web.php';

// ── 7. Dispatch ───────────────────────────────────────────────────────────────
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
