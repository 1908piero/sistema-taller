<?php
echo "[MIGRACION] Iniciando migracion de imagenes...\n";

require_once __DIR__ . '/vendor/autoload.php';

$database = new Config\Database();
$db = $database->getConnection();

if (!$db) {
    echo "[MIGRACION] ERROR: No se pudo conectar a la base de datos.\n";
    exit(1);
}

try {
    $db->exec("ALTER TABLE configuracion MODIFY COLUMN logo LONGTEXT DEFAULT NULL");
    echo "[MIGRACION] OK: configuracion.logo -> LONGTEXT\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "[MIGRACION] Ya aplicado: configuracion.logo\n";
    } else {
        echo "[MIGRACION] AVISO: configuracion.logo - " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("ALTER TABLE productos MODIFY COLUMN imagen LONGTEXT DEFAULT NULL");
    echo "[MIGRACION] OK: productos.imagen -> LONGTEXT\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "[MIGRACION] Ya aplicado: productos.imagen\n";
    } else {
        echo "[MIGRACION] AVISO: productos.imagen - " . $e->getMessage() . "\n";
    }
}

echo "[MIGRACION] Completa.\n";
