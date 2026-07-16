<?php
namespace App\Models;

use PDO;

class Cliente extends BaseModel {
    
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    // --- NUEVO: Obtener datos de un solo cliente ---
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    // --- NUEVO: Historial de Órdenes del cliente ---
    public function getOrdenes($clienteId) {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE cliente_id = :id ORDER BY fecha_recepcion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $clienteId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    // --- NUEVO: Historial de Ventas (POS) del cliente ---
    public function getVentas($clienteId) {
        try {
            $sql = "SELECT * FROM ventas WHERE cliente_id = :id ORDER BY fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $clienteId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    // --- NUEVO: Estadísticas Financieras (LTV - Lifetime Value) ---
    public function getEstadisticas($clienteId) {
        try {
            // 1. Total gastado en Servicios (Órdenes no canceladas)
            $stmt = $this->db->prepare("SELECT SUM(total) as total FROM ordenes_servicio WHERE cliente_id = :id AND estado != 'Cancelada'");
            $stmt->execute([':id' => $clienteId]);
            $totalServicios = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

            // 2. Total gastado en Ventas POS
            $stmt = $this->db->prepare("SELECT SUM(total) as total FROM ventas WHERE cliente_id = :id");
            $stmt->execute([':id' => $clienteId]);
            $totalVentas = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

            return [
                'total_gastado' => $totalServicios + $totalVentas,
                'servicios_count' => $this->countOrdenes($clienteId),
                'ventas_count' => $this->countVentas($clienteId)
            ];
        } catch (\Exception $e) { 
            return ['total_gastado' => 0, 'servicios_count' => 0, 'ventas_count' => 0]; 
        }
    }

    private function countOrdenes($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM ordenes_servicio WHERE cliente_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ)->c;
    }

    private function countVentas($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM ventas WHERE cliente_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ)->c;
    }

    // CU-01: Buscar por código (validar duplicados)
    public function getByCodigo($codigo, $excluirId = null) {
        try {
            $sql = "SELECT id FROM clientes WHERE codigo = :cod";
            if ($excluirId) { $sql .= " AND id != :eid"; }
            $stmt = $this->db->prepare($sql);
            $params = [':cod' => $codigo];
            if ($excluirId) { $params[':eid'] = $excluirId; }
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    // RN-01: Buscar por DNI (validar duplicados)
    public function getByDni($dni, $excluirId = null) {
        try {
            $sql = "SELECT id FROM clientes WHERE dni = :dni";
            if ($excluirId) { $sql .= " AND id != :eid"; }
            $stmt = $this->db->prepare($sql);
            $params = [':dni' => $dni];
            if ($excluirId) { $params[':eid'] = $excluirId; }
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO clientes (codigo, nombre, dni, telefono, email, direccion, estado) VALUES (:codigo, :nombre, :dni, :telefono, :email, :direccion, 1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':codigo' => $data['codigo'],
                ':nombre' => $data['nombre'], ':dni' => $data['dni'] ?? null,
                ':telefono' => $data['telefono'], ':email' => $data['email'],
                ':direccion' => $data['direccion']
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function update($data) {
        try {
            $sql = "UPDATE clientes SET codigo=:codigo, nombre=:nombre, dni=:dni, telefono=:telefono, email=:email, direccion=:direccion WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':codigo' => $data['codigo'],
                ':nombre' => $data['nombre'], ':dni' => $data['dni'] ?? null,
                ':telefono' => $data['telefono'], ':email' => $data['email'],
                ':direccion' => $data['direccion'], ':id' => $data['id']
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function updateStatus($id, $estado) {
        try {
            $stmt = $this->db->prepare("UPDATE clientes SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (\Exception $e) { return false; }
    }
}