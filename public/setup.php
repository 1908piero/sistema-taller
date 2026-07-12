<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    echo "Error de conexión a MySQL. Verifica las variables de entorno.";
    exit;
}

echo "<h2>Instalando base de datos...</h2>";

$conn->exec("SET FOREIGN_KEY_CHECKS = 0");

$sql = file_get_contents(__DIR__ . '/../bk_basededatos.sql');
$sql .= "\n\n" . file_get_contents(__DIR__ . '/../bk_migracion.sql');

$lines = explode("\n", $sql);
$statement = '';
$count = 0;
$errors = [];

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (empty($trimmed)) continue;
    if (str_starts_with($trimmed, '--')) continue;
    if (preg_match('/^\/\*!/', $trimmed)) continue;
    if (preg_match('/^(CREATE DATABASE|USE)/i', $trimmed)) continue;

    $statement .= $line . "\n";

    if (str_ends_with(trim($line), ';')) {
        try {
            $conn->exec($statement);
            $count++;
        } catch (\Exception $e) {
            $errors[] = substr($e->getMessage(), 0, 100);
        }
        $statement = '';
    }
}

$conn->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "<p style='color:green'>✓ $count sentencias ejecutadas.</p>";

if ($errors) {
    echo "<h4>Advertencias (ignorar si son pocas):</h4><pre>";
    foreach (array_slice($errors, 0, 10) as $e) {
        echo htmlspecialchars($e) . "\n";
    }
    echo "</pre>";
}

echo '<p><a href="/" class="btn btn-primary">Ir al inicio</a></p>';
