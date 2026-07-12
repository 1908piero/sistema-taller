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

echo "<h3>Probando INSERT con placa válida via Modelo:</h3>";
try {
    $vehiculo = new Vehiculo();
    $result = $vehiculo->create([
        'cliente_id' => 1,
        'placa' => 'ABC-123',
        'marca' => 'Toyota',
        'modelo' => 'Corolla',
        'año' => 2020,
        'color' => 'Azul',
    ]);
    if ($result) {
        echo "<p style='color:green'>✓ Vehículo creado exitosamente con placa ABC-123</p>";
    } else {
        echo "<p style='color:red'>✗ El modelo Vehiculo::create() devolvió false</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Vehículos actuales:</h3><pre>";
$vehiculos = $vehiculo->getAll();
foreach ($vehiculos as $v) {
    echo "ID: {$v->id} | Placa: {$v->placa} | Marca: {$v->marca} {$v->modelo} | Cliente: {$v->cliente_nombre} | Año: {$v->año}\n";
}
if (!$vehiculos) echo "(ninguno)\n";
echo "</pre>";

echo '<p><a href="/vehiculos">Ir a Gestión de Vehículos</a></p>';
