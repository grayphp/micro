<?php

namespace system\database;

class Database
{
    private $DB;
    public function __construct()
    {
        $this->db = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    }
    public function query($sql, $data = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $stmt;
    }

    public function fetch($sql, $data = [])
    {
        $stmt = $this->query($sql, $data);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
}