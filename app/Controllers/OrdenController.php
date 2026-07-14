<?php
namespace App\Controllers;

use App\Models\Orden;
use App\Models\Cliente;
use App\Models\Producto;
use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrdenController extends BaseController {

    // Listar Órdenes
    public function index() {
        $ordenModel = new Orden();
        $ordenes = $ordenModel->getAll();
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();

        $this->view('ordenes/index', [
            'ordenes' => $ordenes,
            'clientes' => $clientes,
            'titulo' => 'Gestión de Órdenes'
        ]);
    }

    // Ver Detalle / Bitácora
    public function detalle() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /ordenes'); exit; }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        
        if (!$orden) { echo "Orden no encontrada"; exit; }

        $repuestos = $ordenModel->getRepuestos($id);
        $historial = $ordenModel->getHistorial($id);
        $prodModel = new Producto();
        $productos = $prodModel->getAll();

        $this->view('ordenes/detalle', [
            'orden' => $orden,
            'repuestos' => $repuestos,
            'productos' => $productos,
            'historial' => $historial,
            'titulo' => 'Detalle Orden #' . $id
        ]);
    }

    // Crear Nueva Orden
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente_id' => $_POST['cliente_id'],
                'equipo_tipo' => $_POST['equipo_tipo'],
                'equipo_marca' => $_POST['equipo_marca'],
                'equipo_modelo' => $_POST['equipo_modelo'],
                'equipo_serie' => $_POST['equipo_serie'],
                'falla_reportada' => $_POST['falla_reportada'],
                'fecha_promesa' => $_POST['fecha_promesa']
            ];

            $ordenModel = new Orden();
            if ($ordenModel->create($data)) {
                header('Location: /ordenes?msg=guardado');
                exit;
            } else {
                error_log("[ORDEN ERROR] create failed. cliente_id={$data['cliente_id']} equipo={$data['equipo_tipo']} user_id=" . ($_SESSION['user_id'] ?? 'N/A'));
                header('Location: /ordenes?msg=error');
                exit;
            }
        }
    }

    // --- CORREGIDO: Cambiar Estado (Sin pantalla blanca) ---
    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['nuevo_estado'];
            $ordenModel = new Orden();
            
            if ($ordenModel->cambiarEstado($id, $estado)) {
                // Éxito: Redirigir
                if(isset($_SERVER['HTTP_REFERER'])) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                } else {
                    header('Location: /ordenes');
                }
                exit; // Detener script para asegurar redirección
            } else {
                // Error: Mostrar mensaje en lugar de pantalla blanca
                echo "<div style='padding:20px; font-family:sans-serif; text-align:center;'>";
                echo "<h2 style='color:red;'>Error al actualizar el estado</h2>";
                echo "<p>No se pudo guardar el cambio. Posibles causas:</p>";
                echo "<ul style='display:inline-block; text-align:left;'><li>Falta la tabla 'historial_ordenes' en la base de datos.</li><li>Error de conexión.</li></ul>";
                echo "<br><a href='/ordenes'>Volver a intentar</a>";
                echo "</div>";
                exit;
            }
        }
    }

    // Agregar Repuesto al presupuesto
    public function agregarRepuesto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $productoId = $_POST['producto_id'];
            $cantidad = $_POST['cantidad'];
            
            $prodModel = new Producto();
            $producto = $prodModel->getById($productoId);
            $precio = $producto->precio_venta ?? 0;

            $ordenModel = new Orden();
            $ordenModel->addRepuesto([
                'orden_id' => $ordenId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
            ]);
            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    // Eliminar Repuesto
    public function eliminarRepuesto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idDetalle = $_POST['detalle_id'];
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT orden_id FROM orden_repuestos WHERE id = :id");
            $stmt->execute([':id' => $idDetalle]);
            $rep = $stmt->fetch(\PDO::FETCH_OBJ);
            $ordenId = $rep->orden_id ?? null;
            if ($ordenId) {
                $ordenModel = new Orden();
                $ordenModel->removeRepuesto($idDetalle, $ordenId);
            }
            header("Location: /ordenes/detalle?id=" . ($ordenId ?: ''));
            exit;
        }
    }

    // Actualizar costo de Mano de Obra
    public function actualizarManoObra() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $costo = $_POST['costo_mano_obra'];
            $ordenModel = new Orden();
            $ordenModel->actualizarManoObra($ordenId, $costo);
            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    // Guardar Diagnóstico Técnico
    public function guardarDiagnostico() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $texto = $_POST['diagnostico'];
            
            $ordenModel = new Orden();
            $ordenModel->guardarDiagnostico($ordenId, $texto);
            
            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    // --- Imprimir Orden A4 (Con QR) ---
    public function imprimir() {
        $id = $_GET['id'] ?? null;
        if (!$id) { die("ID requerido"); }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        $repuestos = $ordenModel->getRepuestos($id);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/ordenes/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Orden_Servicio_$id.pdf", ["Attachment" => false]);
    }

    // --- Imprimir Etiqueta Sticker (Con QR) ---
    public function etiqueta() {
        $id = $_GET['id'] ?? null;
        if (!$id) { die("ID requerido"); }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/ordenes/etiqueta.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Tamaño personalizado (aprox 8cm x 5cm)
        $dompdf->setPaper([0, 0, 226.77, 141.73], 'landscape'); 
        $dompdf->render();

        $dompdf->stream("Etiqueta_$id.pdf", ["Attachment" => false]);
    }

    // --- Generar Certificado de Garantía ---
    public function garantia() {
        $id = $_GET['id'] ?? null;
        if (!$id) { die("ID requerido"); }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        $repuestos = $ordenModel->getRepuestos($id);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/ordenes/garantia.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Formato Horizontal (Diploma)
        $dompdf->setPaper('A4', 'landscape'); 
        $dompdf->render();

        $dompdf->stream("Garantia_$id.pdf", ["Attachment" => false]);
    }
}