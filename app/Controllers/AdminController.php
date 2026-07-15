<?php
namespace App\Controllers;

class AdminController {

    public function migrar() {
        // Conexión directa, sin autenticación (solo para bootstrap inicial)
        $database = new \Config\Database();
        $db = $database->getConnection();
        if (!$db) { die("Error de conexión"); }

        echo "<pre>";
        echo "[MIGRACION] Sincronizando BD con documento de requisitos...\n\n";

        $ok = function($msg) { echo "<span style='color:green'>[OK]</span> $msg\n"; };
        $warn = function($msg) { echo "<span style='color:orange'>[AVISO]</span> $msg\n"; };

        try {
            $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_ip (ip_address)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $ok("login_attempts");
        } catch (\Exception $e) { $warn($e->getMessage()); }

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
            $ok("audit_log");
        } catch (\Exception $e) { $warn($e->getMessage()); }

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
            $ok("servicios");

            $check = $db->query("SELECT COUNT(*) as cnt FROM servicios")->fetch(\PDO::FETCH_OBJ);
            if ($check->cnt == 0) {
                $db->exec("INSERT INTO servicios (nombre, descripcion, precio, categoria, duracion_estimada) VALUES
                    ('Diagnóstico General', 'Revisión completa del equipo', 35.00, 'diagnostico', '1 hora'),
                    ('Mantenimiento Preventivo', 'Limpieza, pasta térmica, revisión general', 80.00, 'mantenimiento', '2 horas'),
                    ('Mantenimiento Correctivo', 'Reparación de fallas específicas', 120.00, 'mantenimiento', '3 horas'),
                    ('Formateo e Instalación', 'Formateo, instalación de SO y drivers', 60.00, 'software', '2 horas'),
                    ('Respaldo de Datos', 'Copia de seguridad completa', 50.00, 'software', '1 hora')");
                $ok("servicios por defecto insertados");
            }
        } catch (\Exception $e) { $warn($e->getMessage()); }

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
            $ok("orden_servicios");
        } catch (\Exception $e) { $warn($e->getMessage()); }

        $cols = [
            'diagnostico' => "ALTER TABLE ordenes_servicio ADD COLUMN diagnostico TEXT DEFAULT NULL AFTER observaciones_tecnicas",
            'fecha_entrega' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_entrega DATETIME DEFAULT NULL AFTER fecha_promesa",
            'fecha_salida' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_salida DATETIME DEFAULT NULL AFTER fecha_entrega",
        ];
        foreach ($cols as $col => $sql) {
            try {
                $chk = $db->query("SHOW COLUMNS FROM ordenes_servicio LIKE '$col'");
                if ($chk->rowCount() == 0) {
                    $db->exec($sql);
                    $ok("ordenes_servicio.$col");
                } else {
                    $ok("ordenes_servicio.$col ya existe");
                }
            } catch (\Exception $e) { $warn("ordenes_servicio.$col: " . $e->getMessage()); }
        }

        try {
            $chk = $db->query("SHOW COLUMNS FROM productos LIKE 'stock_minimo'");
            if ($chk->rowCount() == 0) {
                $db->exec("ALTER TABLE productos ADD COLUMN stock_minimo INT DEFAULT 5 AFTER stock");
                $db->exec("UPDATE productos SET stock_minimo = 5 WHERE stock_minimo IS NULL");
                $ok("productos.stock_minimo");
            } else {
                $ok("productos.stock_minimo ya existe");
            }
        } catch (\Exception $e) { $warn($e->getMessage()); }

        try {
            $db->exec("ALTER TABLE usuarios MODIFY COLUMN password VARCHAR(255) NOT NULL");
            $ok("usuarios.password -> VARCHAR(255)");
        } catch (\Exception $e) { $warn($e->getMessage()); }

        echo "\n<span style='color:green;font-weight:bold'>[MIGRACION COMPLETA]</span> BD sincronizada con el documento de requisitos.\n";
        echo "</pre>";
    }
}
