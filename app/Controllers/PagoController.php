<?php
namespace App\Controllers;

use App\Models\Pago;
use App\Models\Orden;
use App\Models\Cliente;
use Dompdf\Dompdf;
use Dompdf\Options;

class PagoController extends BaseController {

    public function index() {
        $pagoModel = new Pago();
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $pagos = $pagoModel->getAll();

        if ($fecha) {
            $pagos = array_filter($pagos, function($p) use ($fecha) {
                return substr($p->fecha, 0, 10) === $fecha;
            });
        }

        $resumen = $pagoModel->getResumenDia($fecha);
        $porMetodo = $pagoModel->getPagosPorMetodo($fecha);

        $this->view('pagos/index', [
            'titulo' => 'Caja y Pagos',
            'pagos' => $pagos,
            'resumen' => $resumen,
            'porMetodo' => $porMetodo,
            'fecha' => $fecha
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ref = $_POST['ref'] ?? '/pagos';
            $allowed = ['/pagos', '/pagos/caja'];
            if (!in_array($ref, $allowed)) $ref = '/pagos';

            // RF-08: Validar que la orden exista
            $ordenModel = new Orden();
            $orden = $ordenModel->getById($_POST['orden_id']);
            if (!$orden) {
                header("Location: $ref?msg=orden_invalida");
                exit;
            }

            // RF-08: Validar monto > 0
            $monto = floatval($_POST['monto'] ?? 0);
            if ($monto <= 0) {
                header("Location: $ref?msg=monto_invalido");
                exit;
            }

            // CU-04: Validar método de pago (whitelist)
            $metodosPermitidos = ['efectivo', 'tarjeta', 'transferencia', 'yape', 'plin'];
            $metodoPago = strtolower(trim($_POST['metodo_pago'] ?? ''));
            if (!in_array($metodoPago, $metodosPermitidos)) {
                header("Location: $ref?msg=error");
                exit;
            }

            $data = [
                'orden_id' => $_POST['orden_id'],
                'cliente_id' => $_POST['cliente_id'],
                'monto' => $monto,
                'metodo_pago' => $metodoPago,
                'referencia' => $_POST['referencia'] ?? null,
                'usuario_id' => $_SESSION['user_id'] ?? 1,
            ];

            $pagoModel = new Pago();
            if ($pagoModel->create($data)) {
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('pagos', $id, 'crear', null, $data);
                header("Location: $ref?msg=ok&pago_id=$id");
                exit;
            } elseif ($pagoModel->getByOrden($data['orden_id'])) {
                header("Location: $ref?msg=pago_duplicado");
                exit;
            } else {
                header("Location: $ref?msg=error");
                exit;
            }
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $pagoModel = new Pago();
            $pagoModel->delete($id);
            $this->registrarAuditoria('pagos', $id, 'eliminar', null, null);
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /pagos');
            }
        }
    }

    // RF-09: Generar comprobante de pago en PDF
    public function comprobante() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) { die("ID de pago requerido"); }

            $pagoModel = new Pago();
            $pago = $pagoModel->getById($id);
            if (!$pago) { die("Pago no encontrado"); }

            $sistema = $this->config;

            ob_start();
            require __DIR__ . '/../Views/pagos/comprobante.php';
            $html = ob_get_clean();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, 226.77, 600], 'portrait');
            $dompdf->render();
            $dompdf->stream("COMPROBANTE_{$id}.pdf", ["Attachment" => false]);
            exit;
        } catch (\Exception $e) {
            header('Location: /pagos?msg=error_comprobante');
            exit;
        }
    }

    public function caja() {
        $pagoModel = new Pago();
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $ordenModel = new Orden();
        $ordenes = $ordenModel->getAllByEstado('Cerrada');

        $resumen = $pagoModel->getResumenDia($fecha);

        $this->view('pagos/caja', [
            'titulo' => 'Apertura de Caja',
            'ordenes' => $ordenes,
            'resumen' => $resumen,
            'fecha' => $fecha
        ]);
    }
}
