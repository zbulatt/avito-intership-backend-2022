<?php

namespace App\Models;

use PDO;

class Database
{
    private $host = 'localhost';
    private $user = 'user';
    private $pass = 'pass';
    private $dbname = 'database';

    public function connect(): PDO
    {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname";
        $pdo = new PDO($dsn, $this->user, $this->pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}