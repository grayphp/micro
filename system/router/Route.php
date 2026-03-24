<?php

declare(strict_types=1);

namespace system\router;

/**
 * Router — collects route definitions then dispatches on Route::dispatch().
 *
 * Supported verbs : GET  POST  PUT  PATCH  DELETE
 * Dynamic segments: prefix segment names with $ (e.g. /user/$id)
 * Method spoofing : POST field _method overrides to PUT / PATCH / DELETE
 * Named routes    : pass $name arg, retrieve URL via Route::url($name, $params)
 * Route groups    : Route::group('/prefix', fn() => ..., [MyMiddleware::class])
 */
final class Route
{
    /** @var array<int, array{method: string, path: string, handler: array|callable, middleware: list<string>}> */
    private static array $routes = [];

    /** @var array<string, string> name => path */
    private static array $namedRoutes = [];

    private static string $groupPrefix = '';

    /** @var list<string> */
    private static array $groupMiddleware = [];

    // -------------------------------------------------------------------------
    // Public route registration
    // -------------------------------------------------------------------------

    public static function get(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        self::add('GET', $path, $handler, $name, $middleware);
    }

    public static function post(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        self::add('POST', $path, $handler, $name, $middleware);
    }

    public static function put(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        self::add('PUT', $path, $handler, $name, $middleware);
    }

    public static function patch(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        self::add('PATCH', $path, $handler, $name, $middleware);
    }

    public static function delete(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        self::add('DELETE', $path, $handler, $name, $middleware);
    }

    public static function any(string $path, array|callable $handler, ?string $name = null, array $middleware = []): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $verb) {
            self::add($verb, $path, $handler, $name, $middleware);
        }
    }

    /**
     * Shorthand: register a GET route that renders a view directly.
     */
    public static function view(string $path, string $view, array $data = [], ?string $name = null): void
    {
        self::add('GET', $path, static fn() => view($view, $data), $name, []);
    }

    /**
     * Register a permanent or temporary redirect.
     */
    public static function redirect(string $from, string $to, int $status = 302): void
    {
        self::add('GET', $from, static function () use ($to, $status): never {
            http_response_code($status);
            header("Location: $to");
            exit();
        }, null, []);
    }

    /**
     * Group routes under a common URL prefix and/or middleware set.
     *
     * @param list<string> $middleware
     */
    public static function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $prevPrefix     = self::$groupPrefix;
        $prevMiddleware = self::$groupMiddleware;

        self::$groupPrefix     = $prevPrefix . '/' . trim($prefix, '/');
        self::$groupMiddleware = array_merge($prevMiddleware, $middleware);

        $callback();

        self::$groupPrefix     = $prevPrefix;
        self::$groupMiddleware = $prevMiddleware;
    }

    // -------------------------------------------------------------------------
    // Named route URL generation
    // -------------------------------------------------------------------------

    /**
     * Generate a full URL for a named route.
     *
     * @param array<string, mixed> $params  Dynamic segment values keyed by name (e.g. ['id' => 5])
     */
    public static function url(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \RuntimeException("Named route '{$name}' not found.");
        }

        $path = self::$namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace('$' . $key, (string) $value, $path);
        }

        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . $path;
    }

    // -------------------------------------------------------------------------
    // Dispatch
    // -------------------------------------------------------------------------

    /**
     * Match the current HTTP request against registered routes and execute the handler.
     * Call this once after all routes have been registered.
     */
    public static function dispatch(): never
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // HTML form method-spoofing via hidden _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper((string) $_POST['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofed;
            }
        }

        $requestPath = self::resolveRequestPath();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = self::matchPath($route['path'], $requestPath);

            if ($params === false) {
                continue;
            }

            // CSRF protection for state-changing verbs
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                if (!is_csrf_valid()) {
                    http_response_code(419);
                    echo '<h1>419 — CSRF Token Mismatch</h1>';
                    exit();
                }
            }

            // Run middleware stack
            foreach ($route['middleware'] as $middlewareClass) {
                (new $middlewareClass())->handle();
            }

            // Invoke handler
            if (is_callable($route['handler'])) {
                call_user_func_array($route['handler'], $params);
            } else {
                [$class, $methodName] = $route['handler'];
                call_user_func_array([new $class(), $methodName], $params);
            }

            exit();
        }

        // No route matched
        self::notFound();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private static function add(
        string $method,
        string $path,
        array|callable $handler,
        ?string $name,
        array $middleware,
    ): void {
        $prefix   = self::$groupPrefix;
        $fullPath = '/' . trim($prefix . '/' . ltrim($path, '/'), '/');

        if ($fullPath === '') {
            $fullPath = '/';
        }

        self::$routes[] = [
            'method'     => strtoupper($method),
            'path'       => $fullPath,
            'handler'    => $handler,
            'middleware' => array_merge(self::$groupMiddleware, $middleware),
        ];

        if ($name !== null) {
            self::$namedRoutes[$name] = $fullPath;
        }
    }

    /**
     * Returns an ordered list of captured parameter values, or false on no-match.
     *
     * @return list<string>|false
     */
    private static function matchPath(string $routePath, string $requestPath): array|false
    {
        if ($routePath === $requestPath) {
            return [];
        }

        $routeParts   = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];

        foreach ($routeParts as $i => $segment) {
            if (str_starts_with($segment, '$')) {
                $params[] = $requestParts[$i];
            } elseif ($segment !== $requestParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    /**
     * Derive the path portion of the current request URI,
     * stripped of the APP_URL base path when running in a subdirectory.
     */
    private static function resolveRequestPath(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip subdirectory base if APP_URL contains a path component
        $base = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?? '';
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return '/' . ltrim($path, '/');
    }

    private static function notFound(): never
    {
        http_response_code(404);

        $viewFile = VIEWS_PATH . '404.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<h1>404 — Not Found</h1>';
        }

        exit();
    }
}
