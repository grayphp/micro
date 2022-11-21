<?php

namespace system\database\drivers\sqlite;

use SimpleCrud\Database;

define("SQLITE_PATH", DATABASE_PATH . "/database.sqlite");
class Driver
{
    public $connection;
    public $sql;
    function __construct()
    {

        try {
            if (!file_exists(SQLITE_PATH)) {
                touch(SQLITE_PATH);
            }
            $pdo = new \PDO('sqlite:' . SQLITE_PATH, '', '', array(
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ));
            $this->connection = new Database($pdo);
            $this->sql = $pdo;
        } catch (\Exception $e) {
            $file = $e->getFile();
            $line = $e->getLine();
            $msg = $e->getMessage();
            $etime = date('d/M/Y(h:i a)');
            $error = "<b>Error: </b>" . $msg . "<b> file: </b>" . $file . "<b> line: </b>" . $line . " <b>date: </b>" . $etime;
            exit("<center>database not connected!{$error}</center>");
        }
    }
}