<?php
namespace App\Controllers;

class AdminController extends BaseController {

    public function migrar() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            die("Acceso denegado: solo administradores.");
        }

        echo "<pre>";
        echo "[ADMIN] Ejecutando migracion.php...\n\n";

        try {
            $db = $this->db;

            // 1. Tabla login_attempts
            $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_ip (ip_address)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            echo "[OK] login_attempts\n";

            // 2. Tabla audit_log
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
            echo "[OK] audit_log\n";

            // 3. Tabla servicios
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
            echo "[OK] servicios\n";

            // Insertar servicios por defecto si está vacía
            $check = $db->query("SELECT COUNT(*) as cnt FROM servicios")->fetch(\PDO::FETCH_OBJ);
            if ($check->cnt == 0) {
                $db->exec("INSERT INTO servicios (nombre, descripcion, precio, categoria, duracion_estimada) VALUES
                    ('Diagnóstico General', 'Revisión completa del equipo', 35.00, 'diagnostico', '1 hora'),
                    ('Mantenimiento Preventivo', 'Limpieza, pasta térmica, revisión general', 80.00, 'mantenimiento', '2 horas'),
                    ('Mantenimiento Correctivo', 'Reparación de fallas específicas', 120.00, 'mantenimiento', '3 horas'),
                    ('Formateo e Instalación', 'Formateo, instalación de SO y drivers', 60.00, 'software', '2 horas'),
                    ('Respaldo de Datos', 'Copia de seguridad completa', 50.00, 'software', '1 hora')");
                echo "[OK] servicios por defecto insertados\n";
            }

            // 4. Tabla orden_servicios
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
            echo "[OK] orden_servicios\n";

            // 5. Columnas en ordenes_servicio
            $cols = [
                'diagnostico' => "ALTER TABLE ordenes_servicio ADD COLUMN diagnostico TEXT DEFAULT NULL AFTER observaciones_tecnicas",
                'fecha_entrega' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_entrega DATETIME DEFAULT NULL AFTER fecha_promesa",
                'fecha_salida' => "ALTER TABLE ordenes_servicio ADD COLUMN fecha_salida DATETIME DEFAULT NULL AFTER fecha_entrega",
            ];
            foreach ($cols as $col => $sql) {
                $chk = $db->query("SHOW COLUMNS FROM ordenes_servicio LIKE '$col'");
                if ($chk->rowCount() == 0) {
                    $db->exec($sql);
                    echo "[OK] ordenes_servicio.$col\n";
                } else {
                    echo "[OK] ordenes_servicio.$col ya existe\n";
                }
            }

            // 6. stock_minimo en productos
            $chk = $db->query("SHOW COLUMNS FROM productos LIKE 'stock_minimo'");
            if ($chk->rowCount() == 0) {
                $db->exec("ALTER TABLE productos ADD COLUMN stock_minimo INT DEFAULT 5 AFTER stock");
                $db->exec("UPDATE productos SET stock_minimo = 5 WHERE stock_minimo IS NULL");
                echo "[OK] productos.stock_minimo\n";
            } else {
                echo "[OK] productos.stock_minimo ya existe\n";
            }

            echo "\n[MIGRACION COMPLETA] Todas las estructuras sincronizadas.\n";
        } catch (\Exception $e) {
            echo "[ERROR] " . $e->getMessage() . "\n";
        }

        echo "</pre>";
    }
}
