<?php

declare(strict_types=1);

namespace system\database;

use SimpleCrud\Database as SimpleCrudDatabase;
use system\database\drivers\mysql\Mysql;
use system\database\drivers\sqlite\Driver;

/**
 * Database factory — singleton per request.
 *
 * Access via the DB() / SQL() helpers rather than instantiating directly.
 *
 * @property-read SimpleCrudDatabase $connection  SimpleCrud ORM connection
 * @property-read \PDO               $sql          Raw PDO connection
 */
final class Database
{
    public readonly SimpleCrudDatabase $connection;
    public readonly \PDO $sql;

    private static ?self $instance = null;

    private function __construct()
    {
        $driver = config('database', 'default');

        $driverInstance = match ($driver) {
            'sqlite' => new Driver(),
            default  => new Mysql(),
        };

        $this->connection = $driverInstance->connection;
        $this->sql        = $driverInstance->sql;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
