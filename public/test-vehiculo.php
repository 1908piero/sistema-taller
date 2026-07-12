<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Vehiculo;
use Config\Database;

session_start();
$_SESSION['user_id'] = 1;

$db = new Database();
$conn = $db->getConnection();
if (!$conn) die("Error de conexión");

echo "<h2>Diagnóstico Vehículos</h2>";

echo "<h3>Columnas de vehiculos:</h3><pre>";
$stmt = $conn->query("SHOW COLUMNS FROM vehiculos");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
echo "</pre>";

echo "<h3>Clientes disponibles:</h3><pre>";
$stmt = $conn->query("SELECT id, nombre FROM clientes");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} - {$row['nombre']}\n";
}
echo "</pre>";

echo "<h3>Creando vehículo de prueba...</h3>";
try {
    $vehiculo = new Vehiculo();
    $result = $vehiculo->create([
        'cliente_id' => 1,
        'placa' => 'TEST-01',
        'marca' => 'Toyota',
        'modelo' => 'Corolla',
        'año' => 2020,
        'color' => 'Rojo',
    ]);
    if ($result) {
        echo "<p style='color:green'>✓ Vehículo creado exitosamente</p>";
    } else {
        echo "<p style='color:red'>✗ Error al crear vehículo (revisar logs de MySQL)</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Vehículos actuales:</h3><pre>";
$vehiculos = $vehiculo->getAll();
foreach ($vehiculos as $v) {
    echo "ID: {$v->id} | Placa: {$v->placa} | Cliente: {$v->cliente_nombre}\n";
}
if (!$vehiculos) echo "(ninguno)\n";
echo "</pre>";
