<?php

declare(strict_types=1);

namespace system\http;

/**
 * HTTP response helpers.
 *
 * All public methods send appropriate headers, emit a body, and terminate
 * the script.  Use the standalone helpers (json_response, redirect, abort)
 * defined in global.php for brevity inside controllers and closures.
 */
final class Response
{
    /**
     * Send a JSON response and exit.
     *
     * @param array<string, string> $headers Additional response headers
     */
    public static function json(mixed $data, int $status = 200, array $headers = []): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit();
    }

    /**
     * Send a redirect response and exit.
     */
    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: {$url}");
        exit();
    }

    /**
     * Abort with an HTTP error status.
     * Renders a matching view file from resources/views/{status}.php when available.
     */
    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);

        $default = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'CSRF Token Mismatch',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        $text     = $message ?: ($default[$status] ?? 'Error');
        $viewFile = defined('VIEWS_PATH') ? VIEWS_PATH . "{$status}.php" : null;

        if ($viewFile !== null && file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>{$status} — {$text}</h1>";
        }

        exit();
    }

    /**
     * Send a plain text / HTML response and exit.
     *
     * @param array<string, string> $headers
     */
    public static function make(string $body, int $status = 200, array $headers = []): never
    {
        http_response_code($status);

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $body;
        exit();
    }

    /**
     * Serve a file as a download attachment and exit.
     */
    public static function download(string $filePath, ?string $filename = null): never
    {
        if (!is_file($filePath)) {
            self::abort(404, 'File not found.');
        }

        $filename ??= basename($filePath);
        $size       = filesize($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . ($size !== false ? (string) $size : ''));
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate');
        readfile($filePath);
        exit();
    }

    /**
     * Set a security-oriented response header (call before any output).
     */
    public static function withSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
