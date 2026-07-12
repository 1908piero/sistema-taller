<?php
namespace App\Models;

use PDO;

class Reporte extends BaseModel {
    
    // Obtener Balance Financiero (Ingresos vs Gastos) en un rango de fechas
    public function getBalance($fechaInicio, $fechaFin) {
        
        // A. Sumar VENTAS
        $sqlVentas = "SELECT SUM(total) as total FROM ventas 
                      WHERE fecha BETWEEN :inicio AND :fin";
        $stmt = $this->db->prepare($sqlVentas);
        // Concatenamos horas para cubrir el día completo (00:00:00 a 23:59:59)
        $stmt->execute([':inicio' => "$fechaInicio 00:00:00", ':fin' => "$fechaFin 23:59:59"]);
        $totalVentas = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // B. Sumar SERVICIOS (Órdenes Entregadas)
        // Usamos fecha_recepcion o fecha_promesa? Mejor fecha de creación o de cierre si tuvieras.
        // Por ahora usaremos fecha_recepcion para simplificar, o idealmente deberíamos tener fecha_entrega.
        // Asumiremos que cuenta cuando se recibe el dinero (aprox fecha recepcion en este modelo simple).
        $sqlServicios = "SELECT SUM(total) as total FROM ordenes_servicio 
                         WHERE estado = 'entregado' 
                         AND fecha_recepcion BETWEEN :inicio AND :fin";
        $stmt = $this->db->prepare($sqlServicios);
        $stmt->execute([':inicio' => "$fechaInicio 00:00:00", ':fin' => "$fechaFin 23:59:59"]);
        $totalServicios = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // C. Sumar GASTOS
        $sqlGastos = "SELECT SUM(monto) as total FROM gastos 
                      WHERE fecha BETWEEN :inicio AND :fin";
        $stmt = $this->db->prepare($sqlGastos);
        $stmt->execute([':inicio' => "$fechaInicio 00:00:00", ':fin' => "$fechaFin 23:59:59"]);
        $totalGastos = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        return [
            'ventas' => $totalVentas,
            'servicios' => $totalServicios,
            'ingresos_totales' => $totalVentas + $totalServicios,
            'gastos' => $totalGastos,
            'utilidad' => ($totalVentas + $totalServicios) - $totalGastos
        ];
    }

    // Cantidad de Órdenes por Estado (Para gráfico Pastel)
    public function getOrdenesPorEstado() {
        $sql = "SELECT estado, COUNT(*) as cantidad FROM ordenes_servicio GROUP BY estado";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Productos Top 5
    public function getProductosTop() {
        $sql = "SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
                FROM detalle_ventas dv
                JOIN productos p ON dv.producto_id = p.id
                GROUP BY p.id
                ORDER BY total_vendido DESC
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Historial últimos 6 meses (Ingresos vs Gastos)
    public function getHistorialFinanciero() {
        // Obtenemos los últimos 6 meses
        $historial = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-$i months"));
            $nombreMes = date('M-Y', strtotime("-$i months"));

            // Sumar Ingresos del mes (Ventas + Servicios)
            // Nota: SQL complejo simplificado en lógica PHP para compatibilidad
            $sqlIng = "SELECT 
                (SELECT COALESCE(SUM(total),0) FROM ventas WHERE DATE_FORMAT(fecha, '%Y-%m') = :mes) +
                (SELECT COALESCE(SUM(total),0) FROM ordenes_servicio WHERE estado='entregado' AND DATE_FORMAT(fecha_recepcion, '%Y-%m') = :mes) 
                as total_ingreso";
            
            $stmt = $this->db->prepare($sqlIng);
            $stmt->execute([':mes' => $mes]);
            $ingreso = $stmt->fetch(PDO::FETCH_OBJ)->total_ingreso;

            // Sumar Gastos del mes
            $sqlGas = "SELECT COALESCE(SUM(monto),0) as total_gasto FROM gastos WHERE DATE_FORMAT(fecha, '%Y-%m') = :mes";
            $stmt = $this->db->prepare($sqlGas);
            $stmt->execute([':mes' => $mes]);
            $gasto = $stmt->fetch(PDO::FETCH_OBJ)->total_gasto;

            $historial[] = [
                'mes' => $nombreMes,
                'ingreso' => $ingreso,
                'gasto' => $gasto
            ];
        }
        return $historial;
    }
}