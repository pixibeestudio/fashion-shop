<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // Load env via parse_ini_file if .env exists
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $host = $env['DB_HOST'] ?? '127.0.0.1';
            $db   = $env['DB_NAME'] ?? 'fashion_shop_db';
            $user = $env['DB_USER'] ?? 'root';
            $pass = $env['DB_PASSWORD'] ?? '';
        } else {
            $host = '127.0.0.1';
            $db   = 'fashion_shop_db';
            $user = 'root';
            $pass = '';
        }
        
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
