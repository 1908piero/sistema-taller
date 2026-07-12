<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    echo "Error de conexión a MySQL.";
    exit;
}

echo "<h2>Instalando base de datos...</h2>";

$conn->exec("SET FOREIGN_KEY_CHECKS = 0");

$sql = file_get_contents(__DIR__ . '/../init.sql');

$statements = explode(';', $sql);
$count = 0;
$errors = [];

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt)) continue;
    try {
        $conn->exec($stmt);
        $count++;
    } catch (\Exception $e) {
        $errors[] = substr($e->getMessage(), 0, 100);
    }
}

$conn->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "<p style='color:green'><strong>✓ $count sentencias ejecutadas.</strong></p>";

if ($errors) {
    echo "<p style='color:orange'>" . count($errors) . " advertencias (ignorables):</p><pre>";
    foreach (array_slice($errors, 0, 5) as $e) {
        echo htmlspecialchars($e) . "\n";
    }
    echo "</pre>";
}

echo '<p><a href="/" class="btn btn-primary">Ir al inicio de sesión</a></p>';
