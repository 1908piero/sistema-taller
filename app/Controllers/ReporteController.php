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
            if($estado->estado == 'Abierta') $coloresEstado[] = '#ffc107';
            elseif($estado->estado == 'En proceso') $coloresEstado[] = '#0dcaf0';
            elseif($estado->estado == 'Cerrada') $coloresEstado[] = '#0d6efd';
            elseif($estado->estado == 'Entregada') $coloresEstado[] = '#198754';
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

    // RF-10: Exportar reporte a Excel (CSV)
    public function exportarExcel() {
        $reporteModel = new Reporte();
        $fechaInicio = $_GET['desde'] ?? date('Y-m-01');
        $fechaFin = $_GET['hasta'] ?? date('Y-m-d');
        $reporteCompleto = $reporteModel->getReporteCompleto($fechaInicio, $fechaFin);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_' . $fechaInicio . '_a_' . $fechaFin . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        fputcsv($output, ['REPORTE GERENCIAL - ' . $fechaInicio . ' a ' . $fechaFin]);
        fputcsv($output, []);

        fputcsv($output, ['VENTAS']);
        fputcsv($output, ['#', 'Cliente', 'Fecha', 'Total']);
        foreach ($reporteCompleto['ventas'] as $v) {
            fputcsv($output, [$v->id, $v->cliente_nombre ?? 'General', $v->fecha, number_format($v->total, 2)]);
        }
        fputcsv($output, []);

        fputcsv($output, ['ORDENES ENTREGADAS']);
        fputcsv($output, ['#', 'Cliente', 'Total']);
        foreach ($reporteCompleto['ordenes'] as $o) {
            fputcsv($output, ['ORD-' . str_pad($o->id, 4, '0', STR_PAD_LEFT), $o->cliente_nombre, number_format($o->total, 2)]);
        }
        fputcsv($output, []);

        fputcsv($output, ['ALERTAS STOCK BAJO']);
        fputcsv($output, ['Producto', 'Stock', 'Stock Minimo']);
        foreach ($reporteCompleto['stock_bajo'] as $p) {
            fputcsv($output, [$p->nombre, $p->stock, $p->stock_minimo ?? 5]);
        }

        fclose($output);
        exit;
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
