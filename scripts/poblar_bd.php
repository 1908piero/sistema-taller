<?php
/**
 * Poblar base de datos con 50,000 registros para prueba RNF-07
 * 
 * Uso: php scripts/poblar_bd.php
 * 
 * ADVERTENCIA: Ejecutar SOLO en entorno de pruebas, no en producción.
 */

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

$db = (new Database())->getConnection();
$db->exec("SET FOREIGN_KEY_CHECKS = 0");
$db->exec("TRUNCATE TABLE clientes");
$db->exec("TRUNCATE TABLE vehiculos");
$db->exec("TRUNCATE TABLE ordenes_servicio");
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

$nombres = ['Juan', 'Maria', 'Carlos', 'Ana', 'Pedro', 'Luis', 'Sofia', 'Diego', 'Valentina', 'Miguel'];
$apellidos = ['Garcia', 'Rodriguez', 'Martinez', 'Lopez', 'Gonzalez', 'Perez', 'Sanchez', 'Ramirez', 'Torres', 'Flores'];
$marcas = ['Toyota', 'Honda', 'Nissan', 'Chevrolet', 'Ford', 'Hyundai', 'Kia', 'Volkswagen', 'Mazda', 'Suzuki'];
$modelos = ['Corolla', 'Civic', 'Sentra', 'Spark', 'Focus', 'Tucson', 'Rio', 'Gol', '3', 'Swift'];
$anios = range(2000, 2025);

echo "Insertando 50,000 clientes...\n";
$stmt = $db->prepare("INSERT INTO clientes (codigo, nombre, dni, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?)");
for ($i = 1; $i <= 50000; $i++) {
    $n = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
    $dni = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $tel = '9' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $email = 'cliente' . $i . '@email.com';
    $dir = 'Av. ' . $nombres[array_rand($nombres)] . ' #' . mt_rand(100, 999);
    $codigo = 'CLI-' . str_pad($i, 6, '0', STR_PAD_LEFT);
    $stmt->execute([$codigo, $n, $dni, $tel, $email, $dir]);
    if ($i % 5000 === 0) echo "  $i clientes insertados...\n";
}

echo "Insertando 50,000 vehículos...\n";
$stmt = $db->prepare("INSERT INTO vehiculos (cliente_id, placa, marca, modelo, año, color) VALUES (?, ?, ?, ?, ?, ?)");
for ($i = 1; $i <= 50000; $i++) {
    $cliente_id = mt_rand(1, 50000);
    $placa = chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . '-' . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
    $marca = $marcas[array_rand($marcas)];
    $modelo = $modelos[array_rand($modelos)];
    $anio = $anios[array_rand($anios)];
    $colores = ['Rojo', 'Azul', 'Negro', 'Blanco', 'Plata', 'Gris', 'Verde', 'Naranja'];
    $color = $colores[array_rand($colores)];
    $stmt->execute([$cliente_id, $placa, $marca, $modelo, $anio, $color]);
    if ($i % 5000 === 0) echo "  $i vehículos insertados...\n";
}

echo "¡Base de datos poblada con 50K clientes y 50K vehículos!\n";
echo "Ahora ejecute JMeter para probar RNF-06/RNF-07.\n";
