<?php

namespace system\database;

use system\database\drivers\sqlite\Driver;
use system\database\drivers\mysql\Mysql;

class Database
{
    public $connection;
    function __construct()
    {
        $driver = config('database', 'default');
        try {
            switch ($driver) {
                case 'sqlite':
                    $this->connection = (new Driver())->connection;
                    break;
                case 'mysql':
                    $this->connection = (new Mysql())->connection;
                    break;
                default:
                    # code...
                    break;
            }
        } catch (\Throwable $th) {
            print $th;
        }
    }
}