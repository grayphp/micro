<?php

declare(strict_types=1);

// ─────────────────────────────────────────────────────────────────────────────
// Core path constants  (resolved relative to this file's location)
// ─────────────────────────────────────────────────────────────────────────────
define('APP_ROOT',      realpath(__DIR__ . '/../../') . '/');
define('VIEWS_PATH',    APP_ROOT . 'resources/views/');
define('DATABASE_PATH', APP_ROOT . 'database/');

// ─────────────────────────────────────────────────────────────────────────────
// Configuration
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retrieve a value from a config file.
 *
 * Config files live in config/ and must return an associative array.
 * Results are cached in memory for the duration of the request.
 *
 * @throws \RuntimeException when the file or key is missing.
 */
function config(string $target = 'app', string $key = 'name'): mixed
{
    static $cache = [];

    if (!isset($cache[$target])) {
        $file = APP_ROOT . "config/{$target}.php";

        if (!file_exists($file)) {
            throw new \RuntimeException("Config file '{$target}.php' not found.");
        }

        $cache[$target] = require $file;
    }

    if (!array_key_exists($key, $cache[$target])) {
        throw new \RuntimeException("Config key '{$key}' not found in '{$target}.php'.");
    }

    return $cache[$target][$key];
}

/**
 * Read (or write) an environment variable.
 *
 * String literals "true", "false", "null", "(empty)" are cast to their
 * PHP equivalents so .env values behave intuitively in boolean checks.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true',  '(true)'  => true,
        'false', '(false)' => false,
        'null',  '(null)'  => null,
        'empty', '(empty)' => '',
        default             => $value,
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// Database
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Return the SimpleCrud database connection (singleton per request).
 */
function DB(): \SimpleCrud\Database
{
    return \system\database\Database::getInstance()->connection;
}

/**
 * Return the raw PDO connection (singleton per request).
 */
function SQL(): \PDO
{
    return \system\database\Database::getInstance()->sql;
}

// ─────────────────────────────────────────────────────────────────────────────
// HTTP helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Return the current HTTP Request instance.
 */
function request(): \system\http\Request
{
    return \system\http\Request::current();
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url, int $status = 302): never
{
    \system\http\Response::redirect($url, $status);
}

/**
 * Abort with an HTTP error response and exit.
 */
function abort(int $status, string $message = ''): never
{
    \system\http\Response::abort($status, $message);
}

/**
 * Send a JSON response and exit.
 */
function json_response(mixed $data, int $status = 200): never
{
    \system\http\Response::json($data, $status);
}

// ─────────────────────────────────────────────────────────────────────────────
// Views & assets
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Render a view template.
 *
 * The $view argument uses dot-notation:  "admin.users.index"
 * resolves to resources/views/admin/users/index.php
 *
 * @param array<string, mixed> $data Variables extracted into the template scope.
 * @throws \RuntimeException when the view file does not exist.
 */
function view(string $view, array $data = []): void
{
    $file = VIEWS_PATH . str_replace('.', '/', $view) . '.php';

    if (!file_exists($file)) {
        throw new \RuntimeException("View '{$view}' not found at '{$file}'.");
    }

    extract($data, EXTR_SKIP);
    require $file;
}

/**
 * Return the public URL for an asset.
 *
 * @example  <link href="<?= asset('css/app.css') ?>">
 */
function asset(string $location): string
{
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    return $base . '/asset/' . ltrim($location, '/');
}

/**
 * Generate a full URL for a named route.
 *
 * @param array<string, mixed> $params Dynamic segment values.
 */
function url(string $name, array $params = []): string
{
    return \system\router\Route::url($name, $params);
}

// ─────────────────────────────────────────────────────────────────────────────
// Output escaping
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Echo an HTML-escaped string (safe for use inside HTML attributes and content).
 */
function out(string $text): void
{
    echo htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Return an HTML-escaped string.
 */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─────────────────────────────────────────────────────────────────────────────
// CSRF
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Echo a hidden CSRF token input field.
 * A new token is generated if one does not yet exist in the session.
 */
function set_csrf(): void
{
    echo '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Return the current CSRF token string, generating one if needed.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/**
 * Validate the CSRF token supplied with the current request.
 *
 * Accepts the token in:
 *   - POST field  "csrf"
 *   - Request header  "X-CSRF-TOKEN"
 */
function is_csrf_valid(): bool
{
    $sessionToken  = $_SESSION['csrf'] ?? '';
    $requestToken  = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if ($sessionToken === '' || $requestToken === '') {
        return false;
    }

    return hash_equals($sessionToken, (string) $requestToken);
}

// ─────────────────────────────────────────────────────────────────────────────
// Session flash & old input
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Store or retrieve a flash message.
 *
 * - Set:  flash('success', 'Saved!')
 * - Get:  flash('success')   → reads and clears the value
 */
function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $stored = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $stored;
}

/**
 * Retrieve old input from the previous POST request (form repopulation).
 */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

// ─────────────────────────────────────────────────────────────────────────────
// Arrays
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Determine whether an array is associative (i.e. has at least one string key).
 */
function is_assoc(array $arr): bool
{
    if ($arr === []) {
        return false;
    }

    return array_keys($arr) !== range(0, count($arr) - 1);
}
