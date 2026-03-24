<?php

declare(strict_types=1);

namespace system\database\drivers\mysql;

use PDO;
use SimpleCrud\Database;

/**
 * MySQL / MariaDB driver.
 *
 * Reads connection details from config('database', 'connections')['mysql'].
 * Throws a \PDOException on connection failure — caught by the global handler.
 */
final class Mysql
{
    public readonly Database $connection;
    public readonly PDO $sql;

    public function __construct()
    {
        /** @var array{host: string, port: string, database: string, username: string, password: string, charset: string, unix_socket: string} $cfg */
        $cfg = config('database', 'connections')['mysql'];

        $dsn = $this->buildDsn($cfg);

        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $this->connection = new Database($pdo);
        $this->sql        = $pdo;
    }

    /** @param array<string, string> $cfg */
    private function buildDsn(array $cfg): string
    {
        if (!empty($cfg['unix_socket'])) {
            return sprintf(
                'mysql:unix_socket=%s;dbname=%s;charset=%s',
                $cfg['unix_socket'],
                $cfg['database'],
                $cfg['charset'],
            );
        }

        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset'],
        );
    }
}
