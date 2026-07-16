<?php
namespace App\Controllers;

use App\Models\Configuracion;
use Config\Database;

class BaseController {
    
    protected $db;
    protected $config;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->getConnection();

        if (!isset($_SESSION['user_id']) && static::class !== 'App\Controllers\AuthController') {
            header('Location: /login');
            exit;
        }

        if (isset($_SESSION['user_id'])) {
            $tiempoLimite = 30 * 60;
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempoLimite) {
                session_destroy();
                header('Location: /login');
                exit;
            }
            $_SESSION['last_activity'] = time();
        }

        $configModel = new Configuracion();
        $this->config = $configModel->obtenerConfiguracion();
    }

    protected function procesarImagenSubida($campo, $directorio, $prefijo) {
        if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $tmpName = $_FILES[$campo]['tmp_name'];
        $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $permitidas)) {
            return null;
        }
        $dir = __DIR__ . $directorio;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $nombreArchivo = $prefijo . '_' . time() . '.' . $ext;
        if (move_uploaded_file($tmpName, $dir . $nombreArchivo)) {
            return $nombreArchivo;
        }
        $imgData = @file_get_contents($tmpName);
        if ($imgData !== false) {
            $mime = mime_content_type($tmpName);
            return 'data:' . $mime . ';base64,' . base64_encode($imgData);
        }
        return null;
    }

    // RNF-03 + RN-08: Auditoría de cambios
    protected function registrarAuditoria($tabla, $registroId, $accion, $datosPrevios = null, $datosNuevos = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO audit_log (usuario_id, tabla, registro_id, accion, datos_previos, datos_nuevos, ip_address) 
                                        VALUES (:uid, :tabla, :rid, :acc, :prev, :nuevos, :ip)");
            $stmt->execute([
                ':uid' => $_SESSION['user_id'] ?? null,
                ':tabla' => $tabla,
                ':rid' => $registroId,
                ':acc' => $accion,
                ':prev' => is_string($datosPrevios) ? $datosPrevios : (is_array($datosPrevios) || is_object($datosPrevios) ? json_encode($datosPrevios) : $datosPrevios),
                ':nuevos' => is_string($datosNuevos) ? $datosNuevos : (is_array($datosNuevos) || is_object($datosNuevos) ? json_encode($datosNuevos) : $datosNuevos),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Exception $e) {
            error_log("[AUDIT] Error: " . $e->getMessage());
        }
    }

    protected function view($viewPath, $data = []) {
        extract($data);
        $sistema = $this->config;
        
        $usuario_sesion = [
            'nombre' => $_SESSION['user_name'] ?? 'Usuario',
            'rol' => $_SESSION['user_role'] ?? 'invitado'
        ];

        $file = __DIR__ . "/../Views/" . $viewPath . ".php";
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("Error: La vista '$viewPath' no existe.");
        }
    }
}
