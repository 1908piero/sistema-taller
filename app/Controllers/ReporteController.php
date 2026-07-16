<?php
namespace App\Controllers;

use App\Models\Reporte;
use Dompdf\Dompdf;
use Dompdf\Options;


class ReporteController extends BaseController {

    private function verificarAdmin() {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Admin', 'Jefe'])) {
            header('Location: /dashboard');
            exit;
        }
    }

    public function index() {
        $this->verificarAdmin();
        try {
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

            $hayDatos = !empty($reporteCompleto['ventas']) || !empty($reporteCompleto['ordenes']);

            $this->view('reportes/index', [
                'titulo' => 'Reportes Gerenciales',
                'balance' => $balance,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'chartEstados' => json_encode(['labels' => $labelsEstado, 'data' => $dataEstado, 'colors' => $coloresEstado]),
                'chartProductos' => json_encode(['labels' => $labelsProd, 'data' => $dataProd]),
                'chartHistorial' => json_encode(['labels' => $labelsMes, 'ingreso' => $dataIngreso, 'gasto' => $dataGasto]),
                'reporteCompleto' => $reporteCompleto,
                'hayDatos' => $hayDatos
            ]);
        } catch (\Exception $e) {
            $this->view('reportes/index', [
                'titulo' => 'Reportes Gerenciales',
                'error' => 'MSJ-21: Error al generar el reporte.'
            ]);
        }
    }

    // RF-10: Exportar reporte a Excel (.xlsx)
    public function exportarExcel() {
        $this->verificarAdmin();
        $reporteModel = new Reporte();
        $fechaInicio = $_GET['desde'] ?? date('Y-m-01');
        $fechaFin = $_GET['hasta'] ?? date('Y-m-d');
        $reporteCompleto = $reporteModel->getReporteCompleto($fechaInicio, $fechaFin);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_' . $fechaInicio . '_a_' . $fechaFin . '.xls"');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="UTF-8"><style>td,th{border:1px solid #999;padding:4px;font-size:11pt;font-family:Calibri,sans-serif}</style></head><body>';
        echo '<h2>REPORTE GERENCIAL - ' . $fechaInicio . ' a ' . $fechaFin . '</h2>';

        // --- Hoja: Ventas ---
        echo '<table><caption style="font-weight:bold;font-size:13pt;margin-top:15px">Ventas</caption>';
        echo '<tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th></tr>';
        foreach ($reporteCompleto['ventas'] as $v) {
            echo '<tr><td>' . $v->id . '</td><td>' . ($v->cliente_nombre ?? 'General') . '</td><td>' . $v->fecha . '</td><td>S/ ' . number_format($v->total, 2) . '</td></tr>';
        }
        echo '</table>';

        // --- Hoja: Órdenes ---
        echo '<table><caption style="font-weight:bold;font-size:13pt;margin-top:15px">Órdenes</caption>';
        echo '<tr><th># Orden</th><th>Cliente</th><th>Total</th></tr>';
        foreach ($reporteCompleto['ordenes'] as $o) {
            echo '<tr><td>ORD-' . str_pad($o->id, 4, '0', STR_PAD_LEFT) . '</td><td>' . $o->cliente_nombre . '</td><td>S/ ' . number_format($o->total, 2) . '</td></tr>';
        }
        echo '</table>';

        // --- Hoja: Stock Bajo ---
        echo '<table><caption style="font-weight:bold;font-size:13pt;margin-top:15px">Stock Bajo</caption>';
        echo '<tr><th>Producto</th><th>Stock Actual</th><th>Stock Mínimo</th></tr>';
        foreach ($reporteCompleto['stock_bajo'] as $p) {
            echo '<tr><td>' . $p->nombre . '</td><td>' . $p->stock . '</td><td>' . ($p->stock_minimo ?? 5) . '</td></tr>';
        }
        echo '</table>';

        echo '</body></html>';
        exit;
    }

    // RF-10: Exportar reporte a PDF
    public function exportarPdf() {
        $this->verificarAdmin();
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
