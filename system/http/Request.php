<?php

declare(strict_types=1);

namespace system\http;

/**
 * Immutable HTTP request wrapper.
 *
 * Exposes superglobals through a clean, typed API and provides
 * helpers for input access, header inspection, and basic validation.
 *
 * Access the current request via request() helper or Request::current().
 */
final class Request
{
    public readonly string $method;
    public readonly string $path;
    public readonly string $ip;

    /** @var array<string, mixed> */
    public readonly array $query;

    /** @var array<string, mixed> */
    public readonly array $body;

    /** @var array<string, mixed> */
    public readonly array $files;

    /** @var array<string, string> Lowercase header names */
    public readonly array $headers;

    private static ?self $instance = null;

    public function __construct()
    {
        $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $this->query   = $_GET;
        $this->body    = $_POST;
        $this->files   = $_FILES;
        $this->ip      = $this->resolveIp();
        $this->headers = $this->parseHeaders();
    }

    /**
     * Returns the singleton instance for the current request.
     */
    public static function current(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // -------------------------------------------------------------------------
    // Input access
    // -------------------------------------------------------------------------

    /**
     * Get a value from POST body or GET query string (body takes priority).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get a value from the query string.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get a value from the POST body.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Check if an input key exists in POST or GET.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query);
    }

    /**
     * All merged input (query + body).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Only the specified keys from all merged input.
     *
     * @param  list<string>        $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * All merged input except the specified keys.
     *
     * @param  list<string>        $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Decode a JSON request body.
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === '' || $raw === false) {
            return [];
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    // -------------------------------------------------------------------------
    // Headers
    // -------------------------------------------------------------------------

    public function header(string $key, ?string $default = null): ?string
    {
        $normalized = strtolower(str_replace(['_', ' '], '-', $key));
        return $this->headers[$normalized] ?? $default;
    }

    /**
     * Extract the Bearer token from the Authorization header.
     */
    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization');

        if ($auth !== null && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Introspection
    // -------------------------------------------------------------------------

    public function isAjax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->header('content-type', ''), 'application/json');
    }

    public function isSecure(): bool
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
    }

    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Validate input against a simple rule set.
     * Returns an array of error messages keyed by field name.
     * An empty array means all fields are valid.
     *
     * Supported rules: required | min:N | max:N | email | numeric | url
     *
     * Example:
     *   $errors = $request->validate([
     *       'email'    => 'required|email',
     *       'password' => 'required|min:8',
     *   ]);
     *
     * @param  array<string, string|list<string>> $rules
     * @return array<string, list<string>>
     */
    public function validate(array $rules): array
    {
        $errors = [];
        $data   = $this->all();

        foreach ($rules as $field => $ruleString) {
            $fieldRules = is_array($ruleString)
                ? $ruleString
                : explode('|', $ruleString);

            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                [$ruleName, $ruleParam] = array_pad(explode(':', (string) $rule, 2), 2, null);

                $failed = match ($ruleName) {
                    'required' => $value === null || $value === '',
                    'min'      => is_string($value) && mb_strlen($value) < (int) $ruleParam,
                    'max'      => is_string($value) && mb_strlen($value) > (int) $ruleParam,
                    'email'    => $value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                    'numeric'  => $value !== null && $value !== '' && !is_numeric($value),
                    'url'      => $value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL),
                    default    => false,
                };

                if ($failed) {
                    $errors[$field][] = match ($ruleName) {
                        'required' => "{$field} is required.",
                        'min'      => "{$field} must be at least {$ruleParam} characters.",
                        'max'      => "{$field} must not exceed {$ruleParam} characters.",
                        'email'    => "{$field} must be a valid email address.",
                        'numeric'  => "{$field} must be numeric.",
                        'url'      => "{$field} must be a valid URL.",
                        default    => "{$field} failed validation rule '{$ruleName}'.",
                    };
                }
            }
        }

        return $errors;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            $raw = $_SERVER[$key] ?? '';

            if ($raw !== '') {
                $ip = trim(explode(',', $raw)[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /** @return array<string, string> */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name            = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = (string) $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name            = strtolower(str_replace('_', '-', $key));
                $headers[$name] = (string) $value;
            }
        }

        return $headers;
    }
}
