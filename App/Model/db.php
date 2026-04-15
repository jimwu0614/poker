<?php
class Database {
    private $pdo;

    public function __construct() {

        $config = [
            'db_host' => 'localhost',
            'db_name' => 'your_db_name',
            'db_user' => 'root',
            'db_pass' => '',
            'initial_chips' => 1000
        ];

        try {
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("資料庫連線失敗: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}