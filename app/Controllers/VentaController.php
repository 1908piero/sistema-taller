<?php
namespace App\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use Dompdf\Dompdf;
use Dompdf\Options;

class VentaController extends BaseController {

    public function index() {
        $ventaModel = new Venta();
        $ventas = $ventaModel->getAll();

        $this->view('ventas/index', [
            'ventas' => $ventas,
            'titulo' => 'Historial de Ventas'
        ]);
    }

    public function crear() {
        $prodModel = new Producto();
        $productos = $prodModel->getAll(); 
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();

        $this->view('ventas/crear', [
            'productos' => $productos,
            'clientes' => $clientes,
            'titulo' => 'Nueva Venta / POS'
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productos = json_decode($_POST['productos_json'], true);
            
            if (!$productos || count($productos) === 0) {
                header('Location: /ventas/crear?msg=vacio');
                exit;
            }

            $data = [
                'cliente_id' => $_POST['cliente_id'],
                'total' => $_POST['total_venta']
            ];

            $ventaModel = new Venta();
            $ventaId = $ventaModel->create($data, $productos);

            if ($ventaId && $ventaId !== -1) {
                $this->registrarAuditoria('ventas', $ventaId, 'crear', null, $data);
                header('Location: /ventas?msg=guardado&id=' . $ventaId);
            } elseif ($ventaId === -1) {
                header('Location: /ventas/crear?msg=stock_insuficiente');
            } else {
                header('Location: /ventas/crear?msg=error');
            }
        }
    }

    public function imprimir() {
        $id = $_GET['id'] ?? null;
        if (!$id) die("ID requerido");

        $ventaModel = new Venta();
        $venta = $ventaModel->getById($id);
        $detalles = $ventaModel->getDetalles($id);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/ventas/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 600], 'portrait'); 
        $dompdf->render();
        $dompdf->stream("Ticket_Venta_$id.pdf", ["Attachment" => false]);
    }
}
