<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }
echo "<pre>";
// 1. Eliminar duplicados de placa (conservar el ID más bajo)
$dups = $db->query("SELECT placa, COUNT(*) as cnt, MIN(id) as keep_id FROM vehiculos GROUP BY placa HAVING cnt > 1");
$found = false;
while ($row = $dups->fetch(PDO::FETCH_OBJ)) {
    $found = true;
    $db->exec("DELETE FROM vehiculos WHERE placa = '{$row->placa}' AND id != {$row->keep_id}");
    echo "[LIMPIAR] Placa '{$row->placa}': eliminados duplicados, conservado ID {$row->keep_id}\n";
}
if (!$found) echo "[OK] No hay placas duplicadas\n";

// 2. Agregar UNIQUE INDEX
try {
    $chk = $db->query("SHOW INDEX FROM vehiculos WHERE Key_name = 'idx_placa'");
    if ($chk->rowCount() == 0) {
        $db->exec("ALTER TABLE vehiculos ADD UNIQUE INDEX idx_placa (placa)");
        echo "[OK] UNIQUE INDEX idx_placa agregado a vehiculos.placa\n";
    } else {
        echo "[OK] idx_placa ya existe\n";
    }
} catch (\Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }

echo "Listo.\n</pre>";
