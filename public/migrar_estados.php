<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }
echo "<pre>";
try {
    $mapa = [
        'pendiente'  => 'Abierta',
        'diagnostico' => 'En proceso',
        'reparado'   => 'Cerrada',
        'entregado'  => 'Entregada',
        'cancelado'  => 'Cancelada',
    ];
    $stmt = $db->prepare("UPDATE ordenes_servicio SET estado = :nuevo WHERE estado = :viejo");
    $cont = 0;
    foreach ($mapa as $viejo => $nuevo) {
        $stmt->execute([':nuevo' => $nuevo, ':viejo' => $viejo]);
        $filas = $stmt->rowCount();
        if ($filas > 0) {
            echo "[OK] '$viejo' -> '$nuevo' ($filas ordenes actualizadas)\n";
            $cont += $filas;
        }
    }
    if ($cont == 0) { echo "[OK] Sin ordenes por migrar\n"; } else { echo "\nTotal: $cont ordenes actualizadas.\n"; }
} catch (\Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }
echo "Listo.</pre>";