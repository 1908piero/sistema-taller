<?php
namespace App\Models;

use PDO;

class Venta extends BaseModel {
    
    // Listar ventas recientes
    public function getAll() {
        try {
            $sql = "SELECT v.*, c.nombre as cliente_nombre 
                    FROM ventas v
                    LEFT JOIN clientes c ON v.cliente_id = c.id
                    ORDER BY v.fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Obtener una venta por ID con sus detalles
    public function getById($id) {
        try {
            $sql = "SELECT v.*, c.nombre as cliente_nombre, c.direccion as cliente_direccion, c.telefono as cliente_telefono, c.email as cliente_email
                    FROM ventas v
                    LEFT JOIN clientes c ON v.cliente_id = c.id
                    WHERE v.id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDetalles($ventaId) {
        try {
            $sql = "SELECT dv.*, p.nombre as producto_nombre, p.codigo 
                    FROM detalle_ventas dv
                    INNER JOIN productos p ON dv.producto_id = p.id
                    WHERE dv.venta_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $ventaId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Guardar Venta Completa (Transacción)
    public function create($data, $productosCarrito) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar Cabecera
            $sql = "INSERT INTO ventas (cliente_id, usuario_id, total) VALUES (:cliente_id, 1, :total)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $data['cliente_id'] ?: null, // Si es vacío, guarda NULL
                ':total' => $data['total']
            ]);
            $ventaId = $this->db->lastInsertId();

            // 2. Insertar Detalles y Restar Stock
            $sqlDetalle = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                           VALUES (:venta_id, :prod_id, :cant, :precio, :subtotal)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            $sqlStock = "UPDATE productos SET stock = stock - :cant WHERE id = :id";
            $stmtStock = $this->db->prepare($sqlStock);

            foreach ($productosCarrito as $prod) {
                // Guardar detalle
                $stmtDetalle->execute([
                    ':venta_id' => $ventaId,
                    ':prod_id' => $prod['id'],
                    ':cant' => $prod['cantidad'],
                    ':precio' => $prod['precio'],
                    ':subtotal' => $prod['subtotal']
                ]);

                // Actualizar inventario
                $stmtStock->execute([
                    ':cant' => $prod['cantidad'],
                    ':id' => $prod['id']
                ]);
            }

            $this->db->commit();
            return $ventaId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}