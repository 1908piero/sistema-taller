<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;

session_start();
$_SESSION['user_id'] = 1;

$db = new Database();
$conn = $db->getConnection();
if (!$conn) die("Error de conexión");

echo "<h2>Diagnóstico Vehículos</h2>";

echo "<h3>Ejecutando el mismo SQL del modelo (SIN catch):</h3>";
try {
    $sql = "INSERT INTO vehiculos (cliente_id, placa, marca, modelo, año, color, vin, tipo_motor, observaciones, estado) 
            VALUES (:cliente_id, :placa, :marca, :modelo, :año, :color, :vin, :tipo_motor, :observaciones, 1)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':cliente_id' => 1,
        ':placa' => strtoupper('ABC-123'),
        ':marca' => 'Toyota',
        ':modelo' => 'Corolla',
        ':año' => 2020,
        ':color' => 'Azul',
        ':vin' => null,
        ':tipo_motor' => null,
        ':observaciones' => null,
    ]);
    echo "<p style='color:green'>✓ Éxito! Vehículo insertado.</p>";
} catch (\Exception $e) {
    echo "<p style='color:red'>Error SQL: " . $e->getMessage() . "</p>";
    echo "<p>Code: " . $e->getCode() . "</p>";
}

echo "<h3>Vehículos actuales:</h3><pre>";
$stmt = $conn->query("SELECT v.id, v.placa, v.marca, c.nombre as cliente FROM vehiculos v LEFT JOIN clientes c ON v.cliente_id = c.id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Placa: {$row['placa']}\n";
}
echo "</pre>";
