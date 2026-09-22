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
            die("View not found: {$viewPath}");
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
     * Abort with an HTTP status and a simple message.
     */
    protected function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        echo "<h1>{$code} — {$message}</h1>";
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
