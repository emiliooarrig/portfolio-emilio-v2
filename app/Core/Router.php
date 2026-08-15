<?php

namespace App\Core;

use RuntimeException;

/**
 * Enrutador mínimo: mapea "METODO /ruta/{param}" a "Controlador@accion".
 */
class Router
{
    /** @var array<string, string> */
    private array $routes;

    /**
     * @param array<string, string> $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $definition => $handler) {
            [$routeMethod, $routePath] = array_pad(explode(' ', $definition, 2), 2, '/');

            if (strtoupper($routeMethod) !== $method) {
                continue;
            }

            $params = $this->match($routePath, $path);

            if ($params !== null) {
                $this->invoke($handler, $params);

                return;
            }
        }

        $this->handleNotFound();
    }

    /**
     * @return array<int, string>|null
     */
    private function match(string $routePath, string $path): ?array
    {
        if (! str_contains($routePath, '{')) {
            return $routePath === $path ? [] : null;
        }

        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#u';

        if (preg_match($pattern, $path, $matches) !== 1) {
            return null;
        }

        array_shift($matches);

        return $matches;
    }

    /**
     * @param array<int, string> $params
     */
    private function invoke(string $handler, array $params): void
    {
        [$controllerName, $action] = array_pad(explode('@', $handler, 2), 2, 'index');

        $class = 'App\\Controllers\\' . $controllerName;

        if (! class_exists($class) || ! method_exists($class, $action)) {
            throw new RuntimeException('Controlador o acción inexistente: ' . $handler);
        }

        (new $class())->{$action}(...$params);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);

        $controller = new \App\Controllers\ErrorController();
        $controller->notFoundPage();
    }
}
