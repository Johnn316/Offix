<?php

namespace App\Core;

/**
 * Base Controller — all controllers extend this.
 * Provides view rendering and redirect helpers.
 */
abstract class Controller
{
    /**
     * Render a view file inside the shared layout.
     *
     * @param string $view   Dot-notation path relative to app/views/ e.g. 'contacts.index'
     * @param array  $data   Variables to extract into the view scope
     * @param string $layout Layout file name inside app/views/shared/ (without .php)
     */
    protected function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        // Convert dot notation → path (e.g. 'contacts.index' → 'contacts/index')
        $viewPath = ROOT_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            // The path is a server detail - log it, show the user nothing.
            error_log('View not found: ' . $viewPath);
            $this->abort(500, __('error.generic'));
        }

        // Extract data so view files can use $variable directly
        extract($data, EXTR_SKIP);

        // Capture the inner view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Wrap inside layout
        $layoutPath = ROOT_PATH . '/app/views/shared/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a URL and stop execution.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Current user's role, or '' when not logged in.
     */
    protected function role(): string
    {
        return (string) ($_SESSION['user_role'] ?? '');
    }

    protected function isAdmin(): bool
    {
        return $this->role() === 'admin';
    }

    /**
     * Stop the request unless the current user is an admin.
     * Called from controller constructors so every action is covered —
     * hiding a nav link is not access control.
     */
    protected function requireAdmin(): void
    {
        if ($this->isAdmin()) return;

        if ($this->wantsJson()) {
            $this->json(['error' => 'Forbidden'], 403);
        }
        $this->abort(403, __('error.forbidden'));
    }

    /**
     * True when the caller expects JSON (API route or explicit Accept header).
     */
    protected function wantsJson(): bool
    {
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
        if (str_starts_with('/' . ltrim($uri, '/'), '/api/')) return true;

        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /**
     * Abort with an HTTP status and a safe, user-facing message.
     * $message must be an app string, never exception or driver text.
     */
    protected function abort(int $code = 404, string $message = ''): void
    {
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code($code);

        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $message !== '' ? $message : __('error.generic')]);
            exit;
        }

        $message = $message !== '' ? $message : __('error.generic');
        require ROOT_PATH . '/app/views/shared/error.php';
        exit;
    }

    /**
     * Return JSON (useful for future AJAX endpoints).
     */
    protected function json(mixed $data, int $status = 200): void
    {
        // Discard any PHP warnings buffered before this call (ob_start in index.php).
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
