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

        // String class name + action
        if (!class_exists($handler)) {
            die("Controller not found: {$handler}");
        }
        $controller = new $handler();
        if (!method_exists($controller, $action)) {
            die("Action not found: {$handler}::{$action}");
        }
        call_user_func_array([$controller, $action], $params);
    }
}
