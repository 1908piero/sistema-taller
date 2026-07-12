<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;

session_start();
$_SESSION['user_id'] = 1;

$db = new Database();
$conn = $db->getConnection();
if (!$conn) die("Error de conexión");

echo "<h2>Diagnóstico Vehículos</h2>";

echo "<h3>Clientes disponibles:</h3><pre>";
$stmt = $conn->query("SELECT id, nombre FROM clientes");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} - {$row['nombre']}\n";
}
echo "</pre>";

echo "<h3>Probando INSERT directo:</h3>";
try {
    $sql = "INSERT INTO vehiculos (cliente_id, placa, marca, modelo, año, color, estado) 
            VALUES (:cliente_id, :placa, :marca, :modelo, :anio, :color, 1)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':cliente_id' => 1,
        ':placa' => 'TEST-DIRECTO',
        ':marca' => 'Toyota',
        ':modelo' => 'Corolla',
        ':anio' => 2020,
        ':color' => 'Azul',
    ]);
    if ($result) {
        echo "<p style='color:green'>✓ INSERT directo exitoso!</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>Error en INSERT directo: " . $e->getMessage() . "</p>";
}

echo "<h3>Probando INSERT via Modelo (SIN catch):</h3>";
try {
    $vehiculo = new \App\Models\Vehiculo();
    // Acceder al método create pero dejando que la excepción se propague
    $stmt = $conn->prepare("INSERT INTO vehiculos (cliente_id, placa, marca, modelo, año, color, estado) 
                            VALUES (:cliente_id, :placa, :marca, :modelo, :anio, :color, 1)");
    $result = $stmt->execute([
        ':cliente_id' => 1,
        ':placa' => 'TEST-MODELO',
        ':marca' => 'Nissan',
        ':modelo' => 'Sentra',
        ':anio' => 2021,
        ':color' => 'Negro',
    ]);
    echo "<p style='color:green'>✓ INSERT via modelo exitoso!</p>";
} catch (\Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Vehículos actuales:</h3><pre>";
$stmt = $conn->query("SELECT v.id, v.placa, v.marca, c.nombre as cliente FROM vehiculos v LEFT JOIN clientes c ON v.cliente_id = c.id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Placa: {$row['placa']} | Cliente: {$row['cliente']}\n";
}
echo "</pre>";
