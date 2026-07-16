<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }
echo "<pre>";
try {
    $chk = $db->query("SHOW COLUMNS FROM clientes WHERE Field = 'codigo'");
    if ($chk->rowCount() == 0) {
        $db->exec("ALTER TABLE clientes ADD COLUMN codigo varchar(10) DEFAULT NULL AFTER id");
        echo "[OK] Columna 'codigo' agregada\n";
    } else {
        echo "[OK] 'codigo' ya existe\n";
    }
    // Generar código único para clientes existentes sin código
    $sinCodigo = $db->query("SELECT id FROM clientes WHERE codigo IS NULL OR codigo = ''");
    $stmtUpd = $db->prepare("UPDATE clientes SET codigo = :cod WHERE id = :id");
    while ($row = $sinCodigo->fetch(PDO::FETCH_OBJ)) {
        $cod = 'CLI-' . str_pad($row->id, 6, '0', STR_PAD_LEFT);
        $stmtUpd->execute([':cod' => $cod, ':id' => $row->id]);
        echo "  -> Cliente #{$row->id}: {$cod}\n";
    }
    // UNIQUE index
    $chkIdx = $db->query("SHOW INDEX FROM clientes WHERE Key_name = 'codigo'");
    if ($chkIdx->rowCount() == 0) {
        $db->exec("ALTER TABLE clientes ADD UNIQUE KEY codigo (codigo)");
        echo "[OK] UNIQUE KEY 'codigo' agregada\n";
    } else {
        echo "[OK] UNIQUE KEY 'codigo' ya existe\n";
    }
} catch (\Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }
echo "Listo.</pre>";
