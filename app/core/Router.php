<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    // ── Registration ──────────────────────────────────────────────────────────

    /** Accept either (pattern, ControllerClass::class, 'action') or (pattern, closure) */
    public function get(string $pattern, string|callable $controllerOrClosure, string $action = ''): void
    {
        $this->addRoute('GET', $pattern, $controllerOrClosure, $action);
    }

    public function post(string $pattern, string|callable $controllerOrClosure, string $action = ''): void
    {
        $this->addRoute('POST', $pattern, $controllerOrClosure, $action);
    }

    private function addRoute(string $method, string $pattern, string|callable $handler, string $action): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'action'  => $action,
        ];
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) continue;

            $params = [];
            if ($this->matches($route['pattern'], $uri, $params)) {
                $this->call($route['handler'], $route['action'], $params);
                return;
            }
        }

        http_response_code(404);
        require ROOT_PATH . '/app/views/shared/404.php';
    }

    /** Render a generic 500 without exposing class names or paths. */
    private function fail(): void
    {
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code(500);

        $uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
        if (str_starts_with('/' . ltrim($uri, '/'), '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => __('error.generic')]);
            exit;
        }

        $code    = 500;
        $message = __('error.generic');
        require ROOT_PATH . '/app/views/shared/error.php';
        exit;
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function matches(string $pattern, string $uri, array &$params): bool
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) $params[$key] = $value;
            }
            return true;
        }
        return false;
    }

    private function call(string|callable $handler, string $action, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        // String class name + action. A missing controller or action is a
        // routing bug: log the detail, show the user a plain error page.
        if (!class_exists($handler)) {
            error_log("Controller not found: {$handler}");
            $this->fail();
        }
        $controller = new $handler();
        if (!method_exists($controller, $action)) {
            error_log("Action not found: {$handler}::{$action}");
            $this->fail();
        }
        call_user_func_array([$controller, $action], $params);
    }
}
