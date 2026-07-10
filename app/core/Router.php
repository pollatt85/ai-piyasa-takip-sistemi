<?php
declare(strict_types=1);

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize($uri);
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[a-z_]+\}#', '(\d+)', $route);
            if (preg_match('#^' . $pattern . '$#', $path, $m)) {
                array_shift($m);
                [$controller, $action] = explode('@', $handler);
                (new $controller())->$action(...array_map('intval', $m));
                return;
            }
        }
        http_response_code(404);
        echo '404 — sayfa bulunamadı: ' . e($path);
    }

    private function normalize(string $uri): string
    {
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?? '/');
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $bases = [$script];
        if (str_ends_with($script, '/public')) {
            $bases[] = substr($script, 0, -strlen('/public'));
        }
        foreach ($bases as $base) {
            if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
                break;
            }
        }
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
