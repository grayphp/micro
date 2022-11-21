<?php

namespace system\database;

use system\database\drivers\sqlite\Driver;
use system\database\drivers\mysql\Mysql;

class Database
{
    public $connection;
    public $sql;
    function __construct()
    {
        $driver = config('database', 'default');
        try {
            switch ($driver) {
                case 'sqlite':
                    $this->connection = (new Driver())->connection;
                    $this->sql = (new Driver())->sql;
                    break;
                case 'mysql':
                    $this->connection = (new Mysql())->connection;
                    $this->sql = (new Mysql())->sql;
                    break;
                default:
                    $this->connection = (new Mysql())->connection;
                    $this->sql = (new Mysql())->sql;
                    break;
            }
        } catch (\Throwable $th) {
            print $th;
        }
    }
}