<?php
namespace App\Core;
use PDO;

class Database {
    public static function connect(): PDO {
        $dsn = "mysql:host=127.0.0.1;dbname=phpmvc;charset=utf8mb4";
        return new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}
