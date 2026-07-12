<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;

// Mostrar valores que se usarán
echo "<h2>Config Database en acción:</h2><pre>";

// Simular la lógica del constructor
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'taller_db';
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: '3306';

echo "DSN: mysql:host=$host;port=$port;dbname=$dbname\n";
echo "User: $user\n";
echo "Pass: " . str_repeat('*', strlen($pass)) . "\n\n";

echo "Conectando...\n";
try {
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        echo "✓ Conexión exitosa a MySQL!\n";
        $stmt = $conn->query("SELECT DATABASE() as db, VERSION() as ver, CURRENT_USER() as user");
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        echo "Base de datos: " . $row->db . "\n";
        echo "Versión: " . $row->ver . "\n";
        echo "Usuario: " . $row->user . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
