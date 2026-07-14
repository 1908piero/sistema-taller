<?php
namespace App\Models;

use PDO;

class Orden extends BaseModel {
    
    public function getAll($estado = null) {
        try {
            $sql = "SELECT o.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono 
                    FROM ordenes_servicio o 
                    LEFT JOIN clientes c ON o.cliente_id = c.id";
            if ($estado) {
                $sql .= " WHERE o.estado = :estado";
            }
            $sql .= " ORDER BY o.id DESC";
            $stmt = $this->db->prepare($sql);
            if ($estado) {
                $stmt->execute([':estado' => $estado]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono, c.direccion as cliente_direccion,
                       v.placa as vehiculo_placa, v.marca as vehiculo_marca, v.modelo as vehiculo_modelo,
                       v.`año` as vehiculo_año, v.color as vehiculo_color
                FROM ordenes_servicio o 
                LEFT JOIN clientes c ON o.cliente_id = c.id
                LEFT JOIN vehiculos v ON o.vehiculo_id = v.id
                WHERE o.id = :id LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    public function getAllByEstado($estado) {
        try {
            $sql = "SELECT o.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono
                    FROM ordenes_servicio o 
                    LEFT JOIN clientes c ON o.cliente_id = c.id 
                    WHERE o.estado = :estado 
                    ORDER BY o.fecha_recepcion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO ordenes_servicio (cliente_id, usuario_id, vehiculo_id, tipo_servicio, direccion_servicio, 
                    fecha_recepcion, fecha_promesa, estado, equipo_tipo, equipo_marca, equipo_modelo, equipo_serie, 
                    falla_reportada, costo_mano_obra, total) 
                    VALUES (:cliente_id, :usuario_id, :vehiculo_id, :tipo_servicio, :direccion_servicio, 
                    NOW(), :fecha_promesa, 'pendiente', :equipo_tipo, :equipo_marca, :equipo_modelo, :equipo_serie, 
                    :falla_reportada, 0, 0)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':usuario_id' => $_SESSION['user_id'] ?? 1,
                ':vehiculo_id' => $data['vehiculo_id'] ?? null,
                ':tipo_servicio' => $data['tipo_servicio'] ?? 'taller',
                ':direccion_servicio' => $data['direccion_servicio'] ?? null,
                ':fecha_promesa' => !empty($data['fecha_promesa']) ? $data['fecha_promesa'] : null,
                ':equipo_tipo' => $data['equipo_tipo'] ?? 'vehiculo',
                ':equipo_marca' => $data['equipo_marca'] ?? '',
                ':equipo_modelo' => $data['equipo_modelo'] ?? '',
                ':equipo_serie' => $data['equipo_serie'] ?? '',
                ':falla_reportada' => $data['falla_reportada'] ?? '',
            ]);

            if ($result) {
                $ordenId = $this->db->lastInsertId();
                $this->registrarHistorial($ordenId, 'Orden creada', 'Nueva orden de servicio registrada');
                return $ordenId;
            }
            return false;
        } catch (\Exception $e) { 
            fwrite(STDERR, "[ORDEN MODEL] " . $e->getMessage() . "\n");
            return false; 
        }
    }

    public function cambiarEstado($id, $nuevoEstado) {
        try {
            $stmt = $this->db->prepare("UPDATE ordenes_servicio SET estado = :estado WHERE id = :id");
            $result = $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);

            if ($result) {
                $this->registrarHistorial($id, 'Estado cambiado', 'Nuevo estado: ' . $nuevoEstado);
            }
            return $result;
        } catch (\Exception $e) { return false; }
    }

    public function addRepuesto($data) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO orden_repuestos (orden_id, producto_id, cantidad, precio_unitario, subtotal) 
                                       VALUES (:orden_id, :producto_id, :cantidad, :precio, :subtotal)");
            $stmt->execute([
                ':orden_id' => $data['orden_id'],
                ':producto_id' => $data['producto_id'],
                ':cantidad' => $data['cantidad'],
                ':precio' => $data['precio_unitario'],
                ':subtotal' => $data['cantidad'] * $data['precio_unitario'],
            ]);

            // Descontar stock
            $stmt2 = $this->db->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad");
            $stmt2->execute([':cantidad' => $data['cantidad'], ':id' => $data['producto_id']]);

            // Registrar en kardex
            $stmt3 = $this->db->prepare("SELECT stock FROM productos WHERE id = :id");
            $stmt3->execute([':id' => $data['producto_id']]);
            $stockActual = $stmt3->fetch(PDO::FETCH_OBJ)->stock;

            $stmt4 = $this->db->prepare("INSERT INTO kardex (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_actual, motivo) 
                                        VALUES (:pid, :uid, 'salida', :cant, :stock_ant, :stock_act, :motivo)");
            $stmt4->execute([
                ':pid' => $data['producto_id'], ':uid' => $_SESSION['user_id'] ?? 1,
                ':cant' => $data['cantidad'],
                ':stock_ant' => $stockActual + $data['cantidad'],
                ':stock_act' => $stockActual,
                ':motivo' => 'Uso en orden #' . $data['orden_id'],
            ]);

            $this->recalcularTotal($data['orden_id']);
            $this->registrarHistorial($data['orden_id'], 'Repuesto agregado', 'Producto ID: ' . $data['producto_id'] . ' x' . $data['cantidad']);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function removeRepuesto($id, $ordenId) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM orden_repuestos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $repuesto = $stmt->fetch(PDO::FETCH_OBJ);

            if ($repuesto) {
                // Devolver stock
                $stmt2 = $this->db->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id");
                $stmt2->execute([':cantidad' => $repuesto->cantidad, ':id' => $repuesto->producto_id]);

                $stmt3 = $this->db->prepare("SELECT stock FROM productos WHERE id = :id");
                $stmt3->execute([':id' => $repuesto->producto_id]);
                $stockActual = $stmt3->fetch(PDO::FETCH_OBJ)->stock;

                $stmt4 = $this->db->prepare("INSERT INTO kardex (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_actual, motivo) 
                                            VALUES (:pid, :uid, 'entrada', :cant, :stock_ant, :stock_act, :motivo)");
                $stmt4->execute([
                    ':pid' => $repuesto->producto_id, ':uid' => $_SESSION['user_id'] ?? 1,
                    ':cant' => $repuesto->cantidad,
                    ':stock_ant' => $stockActual - $repuesto->cantidad,
                    ':stock_act' => $stockActual,
                    ':motivo' => 'Devolución de orden #' . $ordenId,
                ]);

                $stmt5 = $this->db->prepare("DELETE FROM orden_repuestos WHERE id = :id");
                $stmt5->execute([':id' => $id]);

                $this->recalcularTotal($ordenId);
                $this->registrarHistorial($ordenId, 'Repuesto eliminado', 'Producto ID: ' . $repuesto->producto_id);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function actualizarManoObra($ordenId, $monto) {
        try {
            $stmt = $this->db->prepare("UPDATE ordenes_servicio SET costo_mano_obra = :monto WHERE id = :id");
            $stmt->execute([':monto' => $monto, ':id' => $ordenId]);
            $this->recalcularTotal($ordenId);
            $this->registrarHistorial($ordenId, 'Mano de obra actualizada', 'S/ ' . $monto);
            return true;
        } catch (\Exception $e) { return false; }
    }

    public function guardarDiagnostico($ordenId, $diagnostico) {
        try {
            $stmt = $this->db->prepare("UPDATE ordenes_servicio SET observaciones_tecnicas = :diag WHERE id = :id");
            $stmt->execute([':diag' => $diagnostico, ':id' => $ordenId]);
            $this->registrarHistorial($ordenId, 'Diagnóstico guardado', substr($diagnostico, 0, 100));
            return true;
        } catch (\Exception $e) { return false; }
    }

    private function recalcularTotal($ordenId) {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(subtotal), 0) as total_repuestos FROM orden_repuestos WHERE orden_id = :id");
            $stmt->execute([':id' => $ordenId]);
            $totalRepuestos = $stmt->fetch(PDO::FETCH_OBJ)->total_repuestos;

            $stmt2 = $this->db->prepare("SELECT costo_mano_obra FROM ordenes_servicio WHERE id = :id");
            $stmt2->execute([':id' => $ordenId]);
            $manoObra = $stmt2->fetch(PDO::FETCH_OBJ)->costo_mano_obra ?? 0;

            $total = $totalRepuestos + $manoObra;
            $stmt3 = $this->db->prepare("UPDATE ordenes_servicio SET total = :total WHERE id = :id");
            $stmt3->execute([':total' => $total, ':id' => $ordenId]);
        } catch (\Exception $e) {}
    }

    private function registrarHistorial($ordenId, $accion, $detalle = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO historial_ordenes (orden_id, usuario_id, accion, detalle) VALUES (:oid, :uid, :acc, :det)");
            $stmt->execute([
                ':oid' => $ordenId,
                ':uid' => $_SESSION['user_id'] ?? 1,
                ':acc' => $accion,
                ':det' => $detalle,
            ]);
        } catch (\Exception $e) {}
    }

    public function getRepuestos($ordenId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, p.nombre as producto_nombre, p.codigo as producto_codigo 
                FROM orden_repuestos r 
                LEFT JOIN productos p ON r.producto_id = p.id 
                WHERE r.orden_id = :id
            ");
            $stmt->execute([':id' => $ordenId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getHistorial($ordenId) {
        try {
            $stmt = $this->db->prepare("
                SELECT h.*, u.nombre as usuario_nombre 
                FROM historial_ordenes h 
                LEFT JOIN usuarios u ON h.usuario_id = u.id 
                WHERE h.orden_id = :id 
                ORDER BY h.fecha DESC
            ");
            $stmt->execute([':id' => $ordenId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getTotalPagos($ordenId) {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE orden_id = :id AND estado = 'completado'");
            $stmt->execute([':id' => $ordenId]);
            return $stmt->fetch(PDO::FETCH_OBJ)->total;
        } catch (\Exception $e) { return 0; }
    }
}
