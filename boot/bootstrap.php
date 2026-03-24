<?php

declare(strict_types=1);

// ─── Output buffering ────────────────────────────────────────────────────────
ob_start();

// ─── Session ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Autoloader (also loads system/helper/global.php via composer `files`) ───
require_once __DIR__ . '/../vendor/autoload.php';

// ─── Environment ─────────────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// ─── Error / exception handling ──────────────────────────────────────────────
if (env('APP_DEBUG') === true) {
    // Pretty error pages in development (requires filp/whoops in require-dev)
    if (class_exists(\Whoops\Run::class)) {
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
    } else {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }
} else {
    // Production: suppress display, log to file, show friendly error pages
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    $logDir = APP_ROOT . 'storage/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    ini_set('error_log', $logDir . 'error.log');

    set_exception_handler(static function (\Throwable $e) use ($logDir): void {
        $status = $e instanceof \system\exception\HttpException
            ? $e->getStatusCode()
            : 500;

        // Log everything except expected HTTP errors below 500
        if ($status >= 500) {
            error_log(sprintf(
                '[%s] %s in %s:%d',
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            ), 3, $logDir . 'error.log');
        }

        http_response_code($status);

        $viewFile = defined('VIEWS_PATH') ? VIEWS_PATH . "{$status}.php" : null;

        if ($viewFile !== null && file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>{$status} Error</h1>";
        }

        exit();
    });
}

// ─── Timezone ────────────────────────────────────────────────────────────────
$timezone = (string) env('TIME_ZONE', 'UTC');

if (!date_default_timezone_set($timezone)) {
    date_default_timezone_set('UTC');
}

// ─── Security headers ────────────────────────────────────────────────────────
\system\http\Response::withSecurityHeaders();

// ─── Routes ──────────────────────────────────────────────────────────────────
require_once APP_ROOT . 'routes/web.php';

// ─── Dispatch ────────────────────────────────────────────────────────────────
\system\router\Route::dispatch();
