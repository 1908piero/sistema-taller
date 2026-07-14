<?php
namespace App\Controllers;

use App\Models\Configuracion;
use Config\Database;

class BaseController {
    
    protected $db;
    protected $config;

    public function __construct() {
        // 1. Iniciar Sesión PHP si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Conexión a BD
        $database = new Database();
        $this->db = $database->getConnection();

        // 3. SEGURIDAD: Verificar Login
        // Si NO existe la sesión de usuario Y NO estamos en el AuthController, redirigir al login.
        // static::class nos dice quién está llamando a este constructor.
        if (!isset($_SESSION['user_id']) && static::class !== 'App\Controllers\AuthController') {
            header('Location: /login');
            exit;
        }

        // 4. Cargar Configuración Global (Solo si estamos logueados o es AuthController)
        // Esto evita errores si intentamos cargar config sin estar autenticados en algunos casos
        $configModel = new Configuracion();
        // Inyectamos la conexión manualmente para evitar bucles si Configuracion hereda de Base
        // Pero como Configuracion hereda de BaseModel (que es seguro), está bien.
        // Sin embargo, para seguridad, instanciamos directamente.
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

    protected function view($viewPath, $data = []) {
        extract($data);
        $sistema = $this->config;
        
        // Pasamos datos del usuario a todas las vistas
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