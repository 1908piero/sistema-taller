<?php
namespace App\Models;

use PDO;

class Producto extends BaseModel {
    
    // Listar todos
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM productos ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Obtener uno
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return null;
        }
    }

    // Obtener Historial (Kardex) de un producto
    public function getKardex($productoId) {
        try {
            $sql = "SELECT k.*, u.nombre as usuario_nombre 
                    FROM kardex k 
                    INNER JOIN usuarios u ON k.usuario_id = u.id 
                    WHERE k.producto_id = :pid 
                    ORDER BY k.fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':pid' => $productoId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Crear producto
    public function create($data) {
        try {
            $sql = "INSERT INTO productos (codigo, nombre, categoria, stock, precio_compra, precio_venta, imagen, estado) 
                    VALUES (:codigo, :nombre, :cat, :stock, :p_compra, :p_venta, :img, 1)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':codigo' => $data['codigo'],
                ':nombre' => $data['nombre'],
                ':cat' => $data['categoria'],
                ':stock' => $data['stock'],
                ':p_compra' => $data['precio_compra'],
                ':p_venta' => $data['precio_venta'],
                ':img' => $data['imagen']
            ]);
        } catch (\Exception $e) {
            error_log("[PRODUCTO MODEL] create error: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar datos básicos
    public function update($data) {
        try {
            $sql = "UPDATE productos SET codigo=:codigo, nombre=:nombre, categoria=:cat, precio_compra=:p_compra, precio_venta=:p_venta";
            
            if ($data['imagen']) {
                $sql .= ", imagen=:img";
            }
            $sql .= " WHERE id=:id";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':codigo' => $data['codigo'], ':nombre' => $data['nombre'], 
                ':cat' => $data['categoria'], ':p_compra' => $data['precio_compra'], 
                ':p_venta' => $data['precio_venta'], ':id' => $data['id']
            ];
            
            if ($data['imagen']) {
                $params[':img'] = $data['imagen'];
            }

            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("[PRODUCTO MODEL] update error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE productos SET estado = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    // --- NUEVO: AJUSTE DE STOCK CON KARDEX ---
    public function ajustarStock($id, $tipo, $cantidad, $motivo, $usuarioId) {
        try {
            $this->db->beginTransaction();

            // 1. Obtener stock actual
            $prod = $this->getById($id);
            $stockAnterior = $prod->stock;
            $stockNuevo = ($tipo == 'entrada') ? ($stockAnterior + $cantidad) : ($stockAnterior - $cantidad);

            if ($stockNuevo < 0) {
                // No permitir stock negativo
                $this->db->rollBack();
                return false; 
            }

            // 2. Actualizar Producto
            $stmtUpdate = $this->db->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmtUpdate->execute([':stock' => $stockNuevo, ':id' => $id]);

            // 3. Insertar en Kardex
            $sqlKardex = "INSERT INTO kardex (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_actual, motivo) 
                          VALUES (:pid, :uid, :tipo, :cant, :ant, :act, :motivo)";
            $stmtKardex = $this->db->prepare($sqlKardex);
            $stmtKardex->execute([
                ':pid' => $id,
                ':uid' => $usuarioId,
                ':tipo' => $tipo,
                ':cant' => $cantidad,
                ':ant' => $stockAnterior,
                ':act' => $stockNuevo,
                ':motivo' => $motivo
            ]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}