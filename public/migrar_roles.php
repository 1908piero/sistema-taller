<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = (new Config\Database())->getConnection();
if (!$db) { die("Error BD"); }
echo "<pre>";
try {
    // 1. Cambiar ENUM de roles
    $db->exec("ALTER TABLE usuarios MODIFY COLUMN rol varchar(30) NOT NULL DEFAULT 'Recepcionista'");
    echo "[OK] Columna rol cambiada a VARCHAR(30)\n";

    // 2. Mapear roles antiguos a nuevos
    $mapa = ['admin' => 'Admin', 'tecnico' => 'Mecánico', 'vendedor' => 'Recepcionista'];
    $stmt = $db->prepare("UPDATE usuarios SET rol = :nuevo WHERE rol = :viejo");
    $cont = 0;
    foreach ($mapa as $viejo => $nuevo) {
        $stmt->execute([':nuevo' => $nuevo, ':viejo' => $viejo]);
        $filas = $stmt->rowCount();
        if ($filas > 0) { echo "[OK] '$viejo' -> '$nuevo' ($filas usuarios)\n"; $cont += $filas; }
    }

    // 3. Aplicar ENUM final
    $db->exec("ALTER TABLE usuarios MODIFY COLUMN rol enum('Jefe','Admin','Recepcionista','Mecánico') NOT NULL DEFAULT 'Recepcionista'");
    echo "[OK] ENUM final aplicado\n";

    if ($cont == 0) { echo "[OK] Sin usuarios por migrar\n"; } else { echo "\nTotal: $cont usuarios migrados.\n"; }
} catch (\Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }
echo "Listo.</pre>";