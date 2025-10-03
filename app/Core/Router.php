<?php
namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public function get(string $pattern, string $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, string $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, string $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, string $handler): void
    {
        $regex = $this->compilePattern($pattern);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    private function compilePattern(string $pattern): string
    {
        $pattern = preg_replace('#:([a-zA-Z_][a-zA-Z0-9_]*)#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . rtrim($pattern, '/') . '/?$#';
    }

    private function stripBase(string $uri): string
    {
        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Remove appFolder/public prefix if present
        $base = rtrim(BASE_URL, '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return $path === '' ? '/' : $path;
    }

    private function matchRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['regex'], $uri, $matches)) {
                // Remove numeric keys from matches
                $params = array_filter($matches, function ($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                return [
                    'handler' => $route['handler'],
                    'params' => $params
                ];
            }
        }

        return null;
    }

    public function dispatch(string $method, string $uri): void
    {
        // Handle PUT and DELETE methods via POST
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? '';
            if (in_array(strtoupper($override), ['PUT', 'DELETE'])) {
                $method = strtoupper($override);
            }
        }
        $path = $this->stripBase($uri);
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                [$controllerName, $action] = explode('@', $route['handler']);
                $controllerClass = '\\App\\Controllers\\' . $controllerName;
                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo "Controller {$controllerClass} not found";
                    return;
                }
                $controller = new $controllerClass();
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo "Action {$action} not found in {$controllerClass}";
                    return;
                }
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }
        http_response_code(404);
        echo '404 Not Found';
    }
}


