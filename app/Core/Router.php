<?php

namespace App\Core;

/**
 * Minimal front-controller router.
 * Supports {param} placeholders and GET/POST verbs.
 */
class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    public function post(string $pattern, array $handler): void
    {
        $this->routes[] = ['POST', $pattern, $handler];
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

        $base = rtrim(BASE_PATH, '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        if ($path === '' ) {
            $path = '/';
        }
        // normalize trailing slash (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }
            $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $path, $matches)) {
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );
                [$class, $action] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../../views/404.php';
    }
}
