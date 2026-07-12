<?php
namespace App\Controllers;

use App\Models\Orden;
use App\Models\Configuracion;

class RastreoController {

    // Nota: No heredamos de BaseController para no forzar el Login.
    // Instanciamos la conexión manualmente.
    protected $db;
    protected $config;

    public function __construct() {
        $database = new \Config\Database();
        $this->db = $database->getConnection();
        
        // Cargamos config para mostrar logo y nombre en la web pública
        $configModel = new Configuracion();
        $this->config = $configModel->obtenerConfiguracion();
    }

    public function index() {
        $ticket = $_GET['ticket'] ?? null;
        $resultado = null;
        $error = null;

        if ($ticket) {
            // Limpiamos la entrada (Si escriben "ORD-0001", nos quedamos solo con "1")
            $idLimpio = str_ireplace('ORD-', '', $ticket);
            $idLimpio = intval($idLimpio);

            $ordenModel = new Orden();
            // Usamos el método getById que ya existe
            $resultado = $ordenModel->getById($idLimpio);

            if (!$resultado) {
                $error = "No se encontró ninguna orden con el ticket #$ticket";
            }
        }

        // Cargamos la vista pública
        // Pasamos $sistema para el logo y nombre
        $sistema = $this->config;
        
        require_once __DIR__ . '/../Views/rastreo/buscar.php';
    }
}