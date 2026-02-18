<?php

declare(strict_types=1);

namespace Th\Core;

use Closure;
use ReflectionException;
use ReflectionFunction;

final class Router
{
    /**
     * @var array<int, array{method: string, pattern: string, handler: callable}>
     */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function dispatch(string $method, string $uri): bool
    {
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $pattern = $this->convertPatternToRegex($route['pattern']);
            $matches = [];

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = [];

            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            $this->invokeHandler($route['handler'], $params);

            return true;
        }

        return false;
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->normalizePath($pattern),
            'handler' => $handler,
        ];
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        return $normalized === '/' ? $normalized : rtrim($normalized, '/');
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);

        return '#^' . (string) $escaped . '$#';
    }

    /**
     * @param array<string, string> $params
     * @throws ReflectionException
     */
    private function invokeHandler(callable $handler, array $params): void
    {
        $reflection = new ReflectionFunction(Closure::fromCallable($handler));

        if ($reflection->getNumberOfParameters() === 0) {
            $handler();

            return;
        }

        $handler($params);
    }
}
