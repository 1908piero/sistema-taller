<?php
namespace App\Controllers;

use App\Models\Configuracion;
use PDO;

class ConfiguracionController extends BaseController {

    public function index() {
        $configModel = new Configuracion();
        $datos = $configModel->obtenerConfiguracion();

        $this->view('configuracion/index', [
            'titulo' => 'Configuración del Sistema',
            'datos' => $datos
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Procesar Logo
            $logo = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $carpetaDestino = __DIR__ . '/../../public/uploads/logo/';
                if (!file_exists($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }
                
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = 'logo_' . time() . '.' . $ext;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                    $logo = $nombreArchivo;
                }
            }

            // Preparar datos
            $data = [
                'nombre_sistema' => $_POST['nombre_sistema'],
                'moneda' => $_POST['moneda'],
                'simbolo_moneda' => $_POST['simbolo_moneda'],
                'impuesto' => $_POST['impuesto'],
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion'],
                'terminos_orden' => $_POST['terminos_orden'],
                'mensaje_ticket' => $_POST['mensaje_ticket'],
                'logo' => $logo
            ];

            $configModel = new Configuracion();
            if ($configModel->update($data)) {
                header('Location: /configuracion?msg=actualizado');
            } else {
                header('Location: /configuracion?msg=error');
            }
        }
    }

    // --- NUEVO: Generar Backup de la Base de Datos ---
    public function backup() {
        // Solo administradores pueden descargar la base de datos
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /?msg=no_autorizado');
            exit;
        }

        try {
            $db = $this->db;
            $tables = [];
            
            // 1. Obtener todas las tablas
            $stmt = $db->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sqlScript = "-- RESPALDO SISTEMA TALLER PRO \n";
            $sqlScript .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
            $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            // 2. Recorrer tablas
            foreach ($tables as $table) {
                // Estructura de la tabla
                $row = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
                $sqlScript .= "\n\n" . $row[1] . ";\n\n";

                // Datos de la tabla
                $rows = $db->query("SELECT * FROM $table");
                $columnCount = $rows->columnCount();

                while ($row = $rows->fetch(PDO::FETCH_NUM)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n\nSET FOREIGN_KEY_CHECKS=1;";

            // 3. Forzar descarga del archivo
            $backupFilename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $backupFilename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($sqlScript));
            
            echo $sqlScript;
            exit;

        } catch (\Exception $e) {
            header('Location: /configuracion?msg=error_backup');
        }
    }
}