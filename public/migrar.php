<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }

echo "<pre>";

try {
    $chk = $db->query("SHOW COLUMNS FROM clientes LIKE 'dni'");
    if ($chk->rowCount() == 0) {
        $db->exec("ALTER TABLE clientes ADD COLUMN dni VARCHAR(20) DEFAULT NULL AFTER nombre");
        $db->exec("ALTER TABLE clientes ADD UNIQUE INDEX idx_dni (dni)");
        echo "[OK] clientes.dni agregado (UNIQUE)\n";
    } else {
        echo "[OK] clientes.dni ya existe\n";
    }
} catch (\Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }

echo "Migracion lista.\n";
echo "</pre>";
