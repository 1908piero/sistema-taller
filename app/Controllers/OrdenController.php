<?php
namespace App\Controllers;

use App\Models\Orden;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Producto;
use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrdenController extends BaseController {

    public function index() {
        $ordenModel = new Orden();
        $ordenes = $ordenModel->getAll();
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();
        $vehiculoModel = new Vehiculo();
        $vehiculos = $vehiculoModel->getAll();

        $this->view('ordenes/index', [
            'ordenes' => $ordenes,
            'clientes' => $clientes,
            'vehiculos' => $vehiculos,
            'titulo' => 'Gestión de Órdenes'
        ]);
    }

    public function detalle() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /ordenes'); exit; }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        
        if (!$orden) { echo "Orden no encontrada"; exit; }

        $repuestos = $ordenModel->getRepuestos($id);
        $servicios = $ordenModel->getServicios($id);
        $historial = $ordenModel->getHistorial($id);
        $prodModel = new Producto();
        $productos = $prodModel->getAll();

        // Cargar servicios del catálogo (RF-05)
        $serviciosCatalogo = [];
        try {
            $stmt = $this->db->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY nombre");
            $serviciosCatalogo = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {}

        $this->view('ordenes/detalle', [
            'orden' => $orden,
            'repuestos' => $repuestos,
            'servicios' => $servicios,
            'serviciosCatalogo' => $serviciosCatalogo,
            'productos' => $productos,
            'historial' => $historial,
            'titulo' => 'Detalle Orden #' . $id
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // RF-03: Validar que cliente exista
            $clienteModel = new Cliente();
            $cliente = $clienteModel->getById($_POST['cliente_id']);
            if (!$cliente) {
                header('Location: /ordenes?msg=cliente_invalido');
                exit;
            }

            // RF-03: Validar vehículo si se proporciona
            $vehiculoId = $_POST['vehiculo_id'] ?? null;
            if ($vehiculoId) {
                $vehiculoModel = new Vehiculo();
                $vehiculo = $vehiculoModel->getById($vehiculoId);
                if (!$vehiculo) {
                    header('Location: /ordenes?msg=vehiculo_invalido');
                    exit;
                }
            }

            $data = [
                'cliente_id' => $_POST['cliente_id'],
                'vehiculo_id' => $vehiculoId,
                'equipo_tipo' => $_POST['equipo_tipo'],
                'equipo_marca' => $_POST['equipo_marca'],
                'equipo_modelo' => $_POST['equipo_modelo'],
                'equipo_serie' => $_POST['equipo_serie'],
                'falla_reportada' => $_POST['falla_reportada'],
                'fecha_promesa' => $_POST['fecha_promesa']
            ];

            $ordenModel = new Orden();
            $ordenId = $ordenModel->create($data);
            if ($ordenId) {
                $this->registrarAuditoria('ordenes_servicio', $ordenId, 'crear', null, $data);
                header('Location: /ordenes?msg=guardado');
                exit;
            } else {
                fwrite(STDERR, "[ORDEN CTRL] create failed. cliente_id={$data['cliente_id']} equipo={$data['equipo_tipo']} user_id=" . ($_SESSION['user_id'] ?? 'N/A') . "\n");
                header('Location: /ordenes?msg=error');
                exit;
            }
        }
    }

    // RF-04 + RN-05: Diagnóstico requerido antes de cambiar a 'reparado' o 'entregado'
    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['nuevo_estado'];
            $ordenModel = new Orden();
            $orden = $ordenModel->getById($id);

            if (!$orden) {
                echo "<div style='padding:20px; font-family:sans-serif; text-align:center;'><h2 style='color:red;'>Orden no encontrada</h2></div>";
                exit;
            }

            // RN-05: Validar que el diagnóstico exista antes de pasar a reparado/entregado
            if (in_array($estado, ['reparado', 'entregado']) && empty(trim($orden->observaciones_tecnicas ?? '')) && empty(trim($orden->diagnostico ?? ''))) {
                echo "<div style='padding:20px; font-family:sans-serif; text-align:center;'>";
                echo "<h2 style='color:red;'>MSJ-28: Diagnóstico requerido (RN-05)</h2>";
                echo "<p><strong>RN-05:</strong> Debe registrar el diagnóstico técnico antes de cambiar el estado a 'Reparado' o 'Entregado'.</p>";
                echo "<p>Agregue el diagnóstico en la sección 'Informe Técnico' del detalle de la orden.</p>";
                echo "<br><a href='/ordenes/detalle?id=$id' class='btn btn-primary'>Ir al detalle</a>";
                echo "</div>";
                exit;
            }

            // RF-05: Validar que exista al menos un servicio registrado antes de reparado/entregado
            if (in_array($estado, ['reparado', 'entregado'])) {
                $servicios = $ordenModel->getServicios($id);
                if (empty($servicios)) {
                    echo "<div style='padding:20px; font-family:sans-serif; text-align:center;'>";
                    echo "<h2 style='color:red;'>MSJ-37: Servicios requeridos (RF-05)</h2>";
                    echo "<p><strong>RF-05:</strong> Debe registrar al menos un servicio en la orden antes de cambiarla a 'Reparado' o 'Entregado'.</p>";
                    echo "<p>Agregue servicios en la sección 'Servicios' del detalle de la orden.</p>";
                    echo "<br><a href='/ordenes/detalle?id=$id' class='btn btn-primary'>Ir al detalle</a>";
                    echo "</div>";
                    exit;
                }
            }

            // RN-05: Si se entrega, registrar fecha_entrega
            $datosNuevos = ['estado' => $estado];
            if ($estado === 'entregado') {
                try {
                    $this->db->prepare("UPDATE ordenes_servicio SET fecha_entrega = NOW() WHERE id = :id")->execute([':id' => $id]);
                    $datosNuevos['fecha_entrega'] = date('Y-m-d H:i:s');
                } catch (\Exception $e) {}
            }

            if ($ordenModel->cambiarEstado($id, $estado)) {
                $this->registrarAuditoria('ordenes_servicio', $id, 'cambiar_estado', "Estado anterior: {$orden->estado}", $datosNuevos);
                if(isset($_SERVER['HTTP_REFERER'])) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                } else {
                    header('Location: /ordenes');
                }
                exit;
            } else {
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
            $this->registrarAuditoria('orden_repuestos', null, 'agregar_repuesto', null, "Orden #$ordenId - Producto #$productoId x$cantidad");
            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

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
                $this->registrarAuditoria('orden_repuestos', $idDetalle, 'eliminar_repuesto', null, "Orden #$ordenId");
            }
            header("Location: /ordenes/detalle?id=" . ($ordenId ?: ''));
            exit;
        }
    }

    public function actualizarManoObra() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $costo = $_POST['costo_mano_obra'];
            $ordenModel = new Orden();
            $ordenModel->actualizarManoObra($ordenId, $costo);
            $this->registrarAuditoria('ordenes_servicio', $ordenId, 'actualizar_mano_obra', null, "Mano de obra: S/ $costo");
            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    public function guardarDiagnostico() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $texto = $_POST['diagnostico'];

            // RF-04: El diagnóstico no puede estar vacío
            if (empty(trim($texto))) {
                header("Location: /ordenes/detalle?id=" . $ordenId . "&msg=diagnostico_requerido");
                exit;
            }

            $ordenModel = new Orden();
            if ($ordenModel->guardarDiagnostico($ordenId, $texto)) {
                $this->registrarAuditoria('ordenes_servicio', $ordenId, 'guardar_diagnostico', null, substr($texto, 0, 200));
                header("Location: /ordenes/detalle?id=" . $ordenId . "&msg=diagnostico_ok");
            } else {
                header("Location: /ordenes/detalle?id=" . $ordenId . "&msg=diagnostico_error");
            }
            exit;
        }
    }

    // RF-05: Agregar servicio del catálogo a la orden
    public function agregarServicio() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenId = $_POST['orden_id'];
            $servicioId = $_POST['servicio_id'];
            $cantidad = $_POST['cantidad'] ?? 1;
            $tecnico = $_POST['tecnico_asignado'] ?? null;

            try {
                $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id = :id AND estado = 1");
                $stmt->execute([':id' => $servicioId]);
                $servicio = $stmt->fetch(\PDO::FETCH_OBJ);

                if ($servicio) {
                    $subtotal = $servicio->precio * $cantidad;
                    $stmt2 = $this->db->prepare("INSERT INTO orden_servicios (orden_id, servicio_id, cantidad, precio_unitario, subtotal, tecnico_asignado) 
                                                  VALUES (:oid, :sid, :cant, :precio, :subtotal, :tecnico)");
                    $stmt2->execute([
                        ':oid' => $ordenId, ':sid' => $servicioId,
                        ':cant' => $cantidad, ':precio' => $servicio->precio,
                        ':subtotal' => $subtotal, ':tecnico' => $tecnico
                    ]);
                $ordenModel = new Orden();
                $ordenModel->recalcularTotal($ordenId);
                $ordenModel->registrarHistorial($ordenId, 'Servicio agregado', $servicio->nombre . ' x' . $cantidad);
                $this->registrarAuditoria('orden_servicios', null, 'agregar_servicio', null, "Orden #$ordenId - Servicio #$servicioId x$cantidad");
                }
            } catch (\Exception $e) {}

            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    // RF-05: Eliminar servicio de la orden
    public function eliminarServicio() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idDetalle = $_POST['detalle_id'];
            $ordenId = $_POST['orden_id'];

            try {
                $stmt = $this->db->prepare("SELECT os.*, s.nombre as servicio_nombre FROM orden_servicios os LEFT JOIN servicios s ON os.servicio_id = s.id WHERE os.id = :id");
                $stmt->execute([':id' => $idDetalle]);
                $detalleServ = $stmt->fetch(\PDO::FETCH_OBJ);
                $stmtDel = $this->db->prepare("DELETE FROM orden_servicios WHERE id = :id");
                $stmtDel->execute([':id' => $idDetalle]);
                $ordenModel = new Orden();
                $ordenModel->recalcularTotal($ordenId);
                $nombreServ = $detalleServ ? ($detalleServ->servicio_nombre ?? 'Servicio #' . $detalleServ->servicio_id) : 'Servicio';
                $ordenModel->registrarHistorial($ordenId, 'Servicio eliminado', $nombreServ);
                $this->registrarAuditoria('orden_servicios', $idDetalle, 'eliminar_servicio', null, "Orden #$ordenId");
            } catch (\Exception $e) {}

            header("Location: /ordenes/detalle?id=" . $ordenId);
            exit;
        }
    }

    public function imprimir() {
        $id = $_GET['id'] ?? null;
        if (!$id) { die("ID requerido"); }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        $repuestos = $ordenModel->getRepuestos($id);
        $servicios = $ordenModel->getServicios($id);
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
        $dompdf->setPaper([0, 0, 226.77, 141.73], 'landscape'); 
        $dompdf->render();

        $dompdf->stream("Etiqueta_$id.pdf", ["Attachment" => false]);
    }

    public function garantia() {
        $id = $_GET['id'] ?? null;
        if (!$id) { die("ID requerido"); }

        $ordenModel = new Orden();
        $orden = $ordenModel->getById($id);
        $repuestos = $ordenModel->getRepuestos($id);
        $servicios = $ordenModel->getServicios($id);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/ordenes/garantia.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); 
        $dompdf->render();

        $dompdf->stream("Garantia_$id.pdf", ["Attachment" => false]);
    }
}
