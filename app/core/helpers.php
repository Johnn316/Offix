<?php

use App\Core\Lang;

/**
 * Translate a key. Falls back to the key itself if not found.
 * Usage: __('nav.contacts')  or  __('flash.created')
 */
function __(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

/**
 * Return the current session's CSRF token, creating one on first use.
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Issue a fresh CSRF token. Called on login so a token observed before
 * authentication is not valid afterwards.
 */
function csrf_rotate(): string
{
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

/**
 * Hidden input for POST forms. Every state-changing form must include this.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Pull the submitted token from a form post, an AJAX header, or a JSON body.
 * The JSON body is needed for navigator.sendBeacon(), which cannot set headers.
 */
function csrf_submitted_token(): string
{
    if (!empty($_POST['_token'])) {
        return (string) $_POST['_token'];
    }
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    // php://input stays re-readable for non-multipart requests, so controllers
    // that decode it later are unaffected.
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
        $body = json_decode($raw, true);
        if (is_array($body) && !empty($body['_token'])) {
            return (string) $body['_token'];
        }
    }

    return '';
}

/**
 * Constant-time check of the submitted token against the session token.
 */
function csrf_verify(): bool
{
    $expected = $_SESSION['_csrf'] ?? '';
    $given    = csrf_submitted_token();

    return $expected !== '' && $given !== '' && hash_equals($expected, $given);
}

/**
 * Render the generic error page (or JSON for API callers) and stop.
 * Used by the global exception and shutdown handlers. Never receives or
 * prints exception text — details go to the error log.
 */
function render_error_page(int $code = 500): void
{
    if (headers_sent()) {
        // Output already began; the best we can do is not append a trace.
        exit;
    }

    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);

    $uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
    $isApi = str_starts_with('/' . ltrim($uri, '/'), '/api/')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isApi) {
        header('Content-Type: application/json');
        echo json_encode(['error' => __('error.generic')]);
        exit;
    }

    $message = __('error.generic');
    require ROOT_PATH . '/app/views/shared/error.php';
    exit;
}
