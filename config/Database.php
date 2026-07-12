<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQL_URL') ? 'localhost' : 'localhost';
        $this->db_name = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'taller_db';
        $this->username = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: '3306';

        if ($url = getenv('MYSQL_URL')) {
            $parts = parse_url($url);
            $this->host = $parts['host'] ?? $this->host;
            $this->username = $parts['user'] ?? $this->username;
            $this->password = $parts['pass'] ?? $this->password;
            $this->db_name = ltrim($parts['path'] ?? '', '/') ?: $this->db_name;
            $this->port = $parts['port'] ?? $this->port;
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}