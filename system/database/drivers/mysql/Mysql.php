<?php

namespace system\database\drivers\mysql;

use SimpleCrud\Database;

class Mysql
{

    private $credentials;
    public $connection;
    public function __construct()
    {
        $this->credentials = config('database', 'connections')['mysql'];
        try {
            $dsn = "mysql:host={$this->credentials['host']};dbname={$this->credentials['database']};port={$this->credentials['port']};charset={$this->credentials['charset']}";
            $pdo = new \PDO($dsn, $this->credentials['username'], $this->credentials['password']);
            $this->connection = new Database($pdo);
        } catch (\Throwable $e) {
            $file = $e->getFile();
            $line = $e->getLine();
            $msg = $e->getMessage();
            $etime = date('d/M/Y(h:i a)');
            $error = "<b>Error: </b>" . $msg . "<b> file: </b>" . $file . "<b> line: </b>" . $line . " <b>date: </b>" . $etime;
            exit("<center>database not connected!{$error}</center>");
        }
    }
}