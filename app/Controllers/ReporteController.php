<?php
namespace App\Controllers;

use App\Models\Reporte;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReporteController extends BaseController {

    public function index() {
        $reporteModel = new Reporte();

        $fechaInicio = $_GET['desde'] ?? date('Y-m-01');
        $fechaFin = $_GET['hasta'] ?? date('Y-m-d');

        $balance = $reporteModel->getBalance($fechaInicio, $fechaFin);
        $estadosOrdenes = $reporteModel->getOrdenesPorEstado();
        $topProductos = $reporteModel->getProductosTop();
        $historial = $reporteModel->getHistorialFinanciero();

        $labelsEstado = []; $dataEstado = []; $coloresEstado = [];
        foreach($estadosOrdenes as $estado) {
            $labelsEstado[] = ucfirst($estado->estado);
            $dataEstado[] = $estado->cantidad;
            if($estado->estado == 'pendiente') $coloresEstado[] = '#ffc107';
            elseif($estado->estado == 'diagnostico') $coloresEstado[] = '#0dcaf0';
            elseif($estado->estado == 'reparado') $coloresEstado[] = '#0d6efd';
            elseif($estado->estado == 'entregado') $coloresEstado[] = '#198754';
            else $coloresEstado[] = '#dc3545';
        }

        $labelsProd = []; $dataProd = [];
        foreach($topProductos as $p) {
            $labelsProd[] = substr($p->nombre, 0, 15);
            $dataProd[] = $p->total_vendido;
        }

        $labelsMes = []; $dataIngreso = []; $dataGasto = [];
        foreach($historial as $h) {
            $labelsMes[] = $h['mes'];
            $dataIngreso[] = $h['ingreso'];
            $dataGasto[] = $h['gasto'];
        }

        // RF-10: Reporte detallado por módulo
        $reporteCompleto = $reporteModel->getReporteCompleto($fechaInicio, $fechaFin);

        $this->view('reportes/index', [
            'titulo' => 'Reportes Gerenciales',
            'balance' => $balance,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'chartEstados' => json_encode(['labels' => $labelsEstado, 'data' => $dataEstado, 'colors' => $coloresEstado]),
            'chartProductos' => json_encode(['labels' => $labelsProd, 'data' => $dataProd]),
            'chartHistorial' => json_encode(['labels' => $labelsMes, 'ingreso' => $dataIngreso, 'gasto' => $dataGasto]),
            'reporteCompleto' => $reporteCompleto
        ]);
    }

    // RF-10: Exportar reporte a PDF
    public function exportarPdf() {
        $reporteModel = new Reporte();
        $fechaInicio = $_GET['desde'] ?? date('Y-m-01');
        $fechaFin = $_GET['hasta'] ?? date('Y-m-d');

        $balance = $reporteModel->getBalance($fechaInicio, $fechaFin);
        $reporteCompleto = $reporteModel->getReporteCompleto($fechaInicio, $fechaFin);
        $sistema = $this->config;

        ob_start();
        require __DIR__ . '/../Views/reportes/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Reporte_{$fechaInicio}_a_{$fechaFin}.pdf", ["Attachment" => false]);
    }
}
