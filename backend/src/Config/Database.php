<?php

namespace App\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Load environment variables from the backend directory.
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Path to backend root
            $dotenv->load();

            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $db   = $_ENV['DB_DATABASE'] ?? 'scandiweb_test';
            $user = $_ENV['DB_USERNAME'] ?? 'root';
            $pass = $_ENV['DB_PASSWORD'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log the error and re-throw, so the application fails fast.
                error_log("Database Connection Error: " . $e->getMessage());
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$instance;
    }
}