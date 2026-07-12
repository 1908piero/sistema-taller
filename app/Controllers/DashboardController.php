<?php
namespace App\Controllers;

use App\Models\Reporte;
use PDO;

class DashboardController extends BaseController {

    public function index() {
        $reporteModel = new Reporte();
        
        // --- CORRECCIÓN ---
        // Usamos la nueva función getBalance() filtrando por el mes actual
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t'); // 't' devuelve el último día del mes
        
        $balance = $reporteModel->getBalance($inicioMes, $finMes);
        $totalIngresos = $balance['ingresos_totales']; // Usamos el total calculado

        // 2. Consultas directas para contadores rápidos
        $db = $this->db;
        
        // Contar Pendientes (Pendiente + Diagnostico)
        $stmt = $db->query("SELECT COUNT(*) as total FROM ordenes_servicio WHERE estado IN ('pendiente', 'diagnostico')");
        $pendientes = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Contar Listos (Reparado)
        $stmt = $db->query("SELECT COUNT(*) as total FROM ordenes_servicio WHERE estado = 'reparado'");
        $listos = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // 3. Obtener las 5 órdenes más recientes
        $sqlRecientes = "SELECT o.id, c.nombre as cliente, o.equipo_modelo, o.estado, o.fecha_recepcion 
                         FROM ordenes_servicio o 
                         INNER JOIN clientes c ON o.cliente_id = c.id 
                         ORDER BY o.id DESC LIMIT 5";
        $stmt = $db->query($sqlRecientes);
        $recientes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // 4. Enviar todo a la vista
        $this->view('dashboard', [
            'titulo' => 'Panel de Control',
            'ingresos' => $totalIngresos,
            'pendientes' => $pendientes,
            'listos' => $listos,
            'recientes' => $recientes
        ]);
    }
}