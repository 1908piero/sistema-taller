<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }
echo "<pre>";
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
