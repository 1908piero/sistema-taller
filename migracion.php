<?php
echo "[MIGRACION] Sincronizando BD con documento de requisitos...\n\n";

require_once __DIR__ . '/vendor/autoload.php';

$database = new Config\Database();
$db = $database->getConnection();

if (!$db) {
    echo "[MIGRACION] ERROR: No se pudo conectar a la base de datos.\n";
    exit(1);
}

// ============================================================
// 1. TABLA login_attempts (RNF-02)
// ============================================================
echo "\n--- login_attempts ---\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_ip (ip_address)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] Tabla login_attempts creada\n";
} catch (PDOException $e) {
    echo "[AVISO] login_attempts: " . $e->getMessage() . "\n";
}

// ============================================================
// 2. TABLA audit_log (RNF-03 + RN-08)
// ============================================================
echo "\n--- audit_log ---\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT DEFAULT NULL,
        tabla VARCHAR(100) NOT NULL,
        registro_id INT DEFAULT NULL,
        accion VARCHAR(50) NOT NULL,
        datos_previos LONGTEXT DEFAULT NULL,
        datos_nuevos LONGTEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tabla (tabla),
        INDEX idx_usuario (usuario_id),
        INDEX idx_fecha (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] Tabla audit_log creada\n";
} catch (PDOException $e) {
    echo "[AVISO] audit_log: " . $e->getMessage() . "\n";
}

// ============================================================
// 3. TABLA servicios (RF-05)
// ============================================================
echo "\n--- servicios ---\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS servicios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(200) NOT NULL,
        descripcion TEXT DEFAULT NULL,
        precio DECIMAL(10,2) NOT NULL DEFAULT 0,
        categoria VARCHAR(100) DEFAULT 'general',
        duracion_estimada VARCHAR(50) DEFAULT NULL,
        estado TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] Tabla servicios creada\n";

    // Insertar servicios por defecto
    $check = $db->query("SELECT COUNT(*) as cnt FROM servicios")->fetch(PDO::FETCH_OBJ);
    if ($check->cnt == 0) {
        $db->exec("INSERT INTO servicios (nombre, descripcion, precio, categoria, duracion_estimada) VALUES
            ('Diagnóstico General', 'Revisión completa del equipo', 35.00, 'diagnostico', '1 hora'),
            ('Mantenimiento Preventivo', 'Limpieza, pasta térmica, revisión general', 80.00, 'mantenimiento', '2 horas'),
            ('Mantenimiento Correctivo', 'Reparación de fallas específicas', 120.00, 'mantenimiento', '3 horas'),
            ('Formateo e Instalación', 'Formateo, instalación de SO y drivers', 60.00, 'software', '2 horas'),
            ('Respaldo de Datos', 'Copia de seguridad completa', 50.00, 'software', '1 hora')");
        echo "[OK] Servicios por defecto insertados\n";
    }
} catch (PDOException $e) {
    echo "[AVISO] servicios: " . $e->getMessage() . "\n";
}

// ============================================================
// 4. TABLA orden_servicios (RF-05 - detalle de servicios x orden)
// ============================================================
echo "\n--- orden_servicios ---\n";
try {
    $db->exec("CREATE TABLE IF NOT EXISTS orden_servicios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        orden_id INT NOT NULL,
        servicio_id INT NOT NULL,
        cantidad INT DEFAULT 1,
        precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
        tecnico_asignado VARCHAR(200) DEFAULT NULL,
        INDEX idx_orden (orden_id),
        INDEX idx_servicio (servicio_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] Tabla orden_servicios creada\n";
} catch (PDOException $e) {
    echo "[AVISO] orden_servicios: " . $e->getMessage() . "\n";
}

// ============================================================
// 5. COLUMNAS faltantes en ordenes_servicio
// ============================================================
echo "\n--- ordenes_servicio columnas ---\n";

$columnasOrden = [
    'diagnostico' => "ALTER TABLE ordenes_servicio ADD COLUMN diagnostico TEXT DEFAULT NULL AFTER observaciones_tecnicas",
    'fecha_entrega' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_entrega DATETIME DEFAULT NULL AFTER fecha_promesa",
    'fecha_salida' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_salida DATETIME DEFAULT NULL AFTER fecha_entrega",
];

foreach ($columnasOrden as $col => $sql) {
    $check = $db->query("SHOW COLUMNS FROM ordenes_servicio LIKE '$col'");
    if ($check->rowCount() == 0) {
        try {
            $db->exec($sql);
            echo "[OK] ordenes_servicio.$col agregada\n";
        } catch (PDOException $e) {
            echo "[AVISO] ordenes_servicio.$col: " . $e->getMessage() . "\n";
        }
    } else {
        echo "[OK] ordenes_servicio.$col ya existe\n";
    }
}

// ============================================================
// 6. COLUMNA stock_minimo en productos
// ============================================================
echo "\n--- productos.stock_minimo ---\n";
$check = $db->query("SHOW COLUMNS FROM productos LIKE 'stock_minimo'");
if ($check->rowCount() == 0) {
    try {
        $db->exec("ALTER TABLE productos ADD COLUMN stock_minimo INT DEFAULT 5 AFTER stock");
        $db->exec("UPDATE productos SET stock_minimo = 5 WHERE stock_minimo IS NULL");
        echo "[OK] productos.stock_minimo agregado\n";
    } catch (PDOException $e) {
        echo "[AVISO] productos.stock_minimo: " . $e->getMessage() . "\n";
    }
} else {
    echo "[OK] productos.stock_minimo ya existe\n";
}

// ============================================================
// 7. COLUMNA password VARCHAR(255) en usuarios (ya debería estar)
// ============================================================
echo "\n--- usuarios.password ---\n";
try {
    $db->exec("ALTER TABLE usuarios MODIFY COLUMN password VARCHAR(255) NOT NULL");
    echo "[OK] usuarios.password -> VARCHAR(255)\n";
} catch (PDOException $e) {
    echo "[AVISO] usuarios.password: " . $e->getMessage() . "\n";
}

echo "\n[MIGRACION] Completa. Todas las estructuras sincronizadas.\n";
