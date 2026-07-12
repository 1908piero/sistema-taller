<?php
namespace App\Controllers;

use App\Models\Reporte;

class ReporteController extends BaseController {

    public function index() {
        $reporteModel = new Reporte();

        // 1. Manejo de Fechas (Filtro)
        // Por defecto: Desde el primer día del mes hasta hoy
        $fechaInicio = $_GET['desde'] ?? date('Y-m-01');
        $fechaFin = $_GET['hasta'] ?? date('Y-m-d');

        // 2. Obtener Balance usando las fechas
        $balance = $reporteModel->getBalance($fechaInicio, $fechaFin);

        // 3. Obtener Datos para Gráficos
        $estadosOrdenes = $reporteModel->getOrdenesPorEstado();
        $topProductos = $reporteModel->getProductosTop();
        $historial = $reporteModel->getHistorialFinanciero();

        // 4. Preparar JSON para Chart.js
        
        // Gráfico Pastel (Estados)
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

        // Gráfico Barras (Productos)
        $labelsProd = []; $dataProd = [];
        foreach($topProductos as $p) {
            $labelsProd[] = substr($p->nombre, 0, 15);
            $dataProd[] = $p->total_vendido;
        }

        // Gráfico Línea (Ingresos vs Gastos)
        $labelsMes = []; $dataIngreso = []; $dataGasto = [];
        foreach($historial as $h) {
            $labelsMes[] = $h['mes'];
            $dataIngreso[] = $h['ingreso'];
            $dataGasto[] = $h['gasto'];
        }

        $this->view('reportes/index', [
            'titulo' => 'Reportes Financieros',
            'balance' => $balance,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            
            // JSONs
            'chartEstados' => json_encode(['labels' => $labelsEstado, 'data' => $dataEstado, 'colors' => $coloresEstado]),
            'chartProductos' => json_encode(['labels' => $labelsProd, 'data' => $dataProd]),
            'chartHistorial' => json_encode(['labels' => $labelsMes, 'ingreso' => $dataIngreso, 'gasto' => $dataGasto])
        ]);
    }
}