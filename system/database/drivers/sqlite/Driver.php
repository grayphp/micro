<?php

declare(strict_types=1);

namespace system\database\drivers\sqlite;

use PDO;
use SimpleCrud\Database;

/**
 * SQLite driver.
 *
 * Creates the database file and its parent directory if they do not exist.
 * Throws a \PDOException on connection failure — caught by the global handler.
 */
final class Driver
{
    public readonly Database $connection;
    public readonly PDO $sql;

    public function __construct()
    {
        $path = $this->resolvePath();

        $this->ensureDirectoryExists($path);

        $pdo = new PDO('sqlite:' . $path, options: [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $this->connection = new Database($pdo);
        $this->sql        = $pdo;
    }

    private function resolvePath(): string
    {
        $configured = config('database', 'connections')['sqlite']['database'] ?? '';

        return $configured !== '' ? $configured : DATABASE_PATH . 'database.sqlite';
    }

    private function ensureDirectoryExists(string $path): void
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
