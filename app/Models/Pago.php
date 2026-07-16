<?php
namespace App\Models;

use PDO;

class Pago extends BaseModel {

    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre,
                       o.id as orden_id
                FROM pagos p 
                LEFT JOIN clientes c ON p.cliente_id = c.id 
                LEFT JOIN usuarios u ON p.usuario_id = u.id 
                LEFT JOIN ordenes_servicio o ON p.orden_id = o.id
                ORDER BY p.fecha DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre
                FROM pagos p 
                LEFT JOIN clientes c ON p.cliente_id = c.id 
                LEFT JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.id = :id LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    public function getByOrden($ordenId) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.nombre as usuario_nombre
                FROM pagos p 
                LEFT JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.orden_id = :id 
                ORDER BY p.fecha DESC
            ");
            $stmt->execute([':id' => $ordenId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO pagos (orden_id, cliente_id, monto, metodo_pago, referencia, estado, usuario_id) 
                    VALUES (:orden_id, :cliente_id, :monto, :metodo_pago, :referencia, 'completado', :usuario_id)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':orden_id' => $data['orden_id'],
                ':cliente_id' => $data['cliente_id'],
                ':monto' => $data['monto'],
                ':metodo_pago' => $data['metodo_pago'],
                ':referencia' => $data['referencia'] ?? null,
                ':usuario_id' => $data['usuario_id'],
            ]);

            if ($result) {
                // Actualizar estado de la orden a 'Entregada' si estaba 'Cerrada'
                $stmt2 = $this->db->prepare("UPDATE ordenes_servicio SET estado = 'Entregada' WHERE id = :id AND estado = 'Cerrada'");
                $stmt2->execute([':id' => $data['orden_id']]);
            }

            return $result;
        } catch (\Exception $e) { return false; }
    }

    public function getResumenDia($fecha = null) {
        $fecha = $fecha ?: date('Y-m-d');
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_pagos, COALESCE(SUM(monto), 0) as total_monto,
                       GROUP_CONCAT(DISTINCT metodo_pago SEPARATOR ', ') as metodos
                FROM pagos 
                WHERE DATE(fecha) = :fecha AND estado = 'completado'
            ");
            $stmt->execute([':fecha' => $fecha]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { 
            $r = new \stdClass();
            $r->total_pagos = 0; $r->total_monto = 0; $r->metodos = '';
            return $r;
        }
    }

    public function getPagosPorMetodo($fecha = null) {
        $fecha = $fecha ?: date('Y-m-d');
        try {
            $stmt = $this->db->prepare("
                SELECT metodo_pago, COUNT(*) as cantidad, SUM(monto) as total
                FROM pagos 
                WHERE DATE(fecha) = :fecha AND estado = 'completado'
                GROUP BY metodo_pago
            ");
            $stmt->execute([':fecha' => $fecha]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM pagos WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) { return false; }
    }
}
