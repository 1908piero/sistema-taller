<?php
namespace App\Controllers;

use App\Models\Pago;
use App\Models\Orden;
use App\Models\Cliente;

class PagoController extends BaseController {

    public function index() {
        $pagoModel = new Pago();
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $pagos = $pagoModel->getAll();

        // Filtrar por fecha si se especifica
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
            $data = [
                'orden_id' => $_POST['orden_id'],
                'cliente_id' => $_POST['cliente_id'],
                'monto' => $_POST['monto'],
                'metodo_pago' => $_POST['metodo_pago'],
                'referencia' => $_POST['referencia'] ?? null,
                'usuario_id' => $_SESSION['user_id'] ?? 1,
            ];

            $pagoModel = new Pago();
            if ($pagoModel->create($data)) {
                $ref = $_POST['ref'] ?? '/pagos';
                $allowed = ['/pagos', '/pagos/caja'];
                if (!in_array($ref, $allowed)) $ref = '/pagos';
                header("Location: $ref");
                exit;
            } else {
                header('Location: /pagos?msg=error');
                exit;
            }
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $pagoModel = new Pago();
            $pagoModel->delete($id);
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /pagos');
            }
        }
    }

    public function caja() {
        $pagoModel = new Pago();
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $ordenModel = new Orden();
        // Ordenes reparadas listas para pagar
        $ordenes = $ordenModel->getAllByEstado('reparado');

        $resumen = $pagoModel->getResumenDia($fecha);

        $this->view('pagos/caja', [
            'titulo' => 'Apertura de Caja',
            'ordenes' => $ordenes,
            'resumen' => $resumen,
            'fecha' => $fecha
        ]);
    }
}
