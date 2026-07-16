<?php
namespace App\Controllers;

use App\Models\Reporte;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // --- Hoja: Ventas ---
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ventas');
        $sheet->setCellValue('A1', 'REPORTE GERENCIAL - ' . $fechaInicio . ' a ' . $fechaFin);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A3', '#');
        $sheet->setCellValue('B3', 'Cliente');
        $sheet->setCellValue('C3', 'Fecha');
        $sheet->setCellValue('D3', 'Total');
        $sheet->getStyle('A3:D3')->getFont()->setBold(true);
        $fila = 4;
        foreach ($reporteCompleto['ventas'] as $v) {
            $sheet->setCellValue('A' . $fila, $v->id);
            $sheet->setCellValue('B' . $fila, $v->cliente_nombre ?? 'General');
            $sheet->setCellValue('C' . $fila, $v->fecha);
            $sheet->setCellValue('D' . $fila, number_format($v->total, 2));
            $fila++;
        }
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Hoja: Órdenes ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Órdenes');
        $sheet2->setCellValue('A1', '# Orden');
        $sheet2->setCellValue('B1', 'Cliente');
        $sheet2->setCellValue('C1', 'Total');
        $sheet2->getStyle('A1:C1')->getFont()->setBold(true);
        $fila = 2;
        foreach ($reporteCompleto['ordenes'] as $o) {
            $sheet2->setCellValue('A' . $fila, 'ORD-' . str_pad($o->id, 4, '0', STR_PAD_LEFT));
            $sheet2->setCellValue('B' . $fila, $o->cliente_nombre);
            $sheet2->setCellValue('C' . $fila, number_format($o->total, 2));
            $fila++;
        }
        foreach (range('A', 'C') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Hoja: Stock Bajo ---
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Stock Bajo');
        $sheet3->setCellValue('A1', 'Producto');
        $sheet3->setCellValue('B1', 'Stock Actual');
        $sheet3->setCellValue('C1', 'Stock Mínimo');
        $sheet3->getStyle('A1:C1')->getFont()->setBold(true);
        $fila = 2;
        foreach ($reporteCompleto['stock_bajo'] as $p) {
            $sheet3->setCellValue('A' . $fila, $p->nombre);
            $sheet3->setCellValue('B' . $fila, $p->stock);
            $sheet3->setCellValue('C' . $fila, $p->stock_minimo ?? 5);
            $fila++;
        }
        foreach (range('A', 'C') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Reporte_' . $fechaInicio . '_a_' . $fechaFin . '.xlsx"');
        $writer->save('php://output');
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
