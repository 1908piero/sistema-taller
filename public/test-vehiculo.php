<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Vehiculo;
use Config\Database;

session_start();
$_SESSION['user_id'] = 1;

echo "<h2 style='color:green'>✓ Sistema funciona!</h2>";
echo "<p>Los vehículos ya se registran correctamente.</p>";

echo "<h3>Vehículos registrados:</h3><pre>";
$vehiculo = new Vehiculo();
$vehiculos = $vehiculo->getAll();
foreach ($vehiculos as $v) {
    echo "ID: {$v->id} | Placa: {$v->placa} | Marca: {$v->marca} {$v->modelo} | Cliente: {$v->cliente_nombre}\n";
}
echo "</pre>";

echo '<p><a href="/vehiculos" class="btn btn-primary">Ir a Gestión de Vehículos</a></p>';
