<?php
namespace App\Models;

use PDO;

class Vehiculo extends BaseModel {

    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT v.*, c.nombre as cliente_nombre 
                FROM vehiculos v 
                LEFT JOIN clientes c ON v.cliente_id = c.id 
                ORDER BY v.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT v.*, c.nombre as cliente_nombre 
                FROM vehiculos v 
                LEFT JOIN clientes c ON v.cliente_id = c.id 
                WHERE v.id = :id LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    public function getByCliente($clienteId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehiculos WHERE cliente_id = :id ORDER BY id DESC");
            $stmt->execute([':id' => $clienteId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function searchByPlaca($placa) {
        try {
            $stmt = $this->db->prepare("
                SELECT v.*, c.nombre as cliente_nombre 
                FROM vehiculos v 
                LEFT JOIN clientes c ON v.cliente_id = c.id 
                WHERE v.placa LIKE :placa 
                ORDER BY v.id DESC LIMIT 10
            ");
            $stmt->execute([':placa' => '%' . $placa . '%']);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, vin, tipo_motor, observaciones, estado) 
                    VALUES (:cliente_id, :placa, :marca, :modelo, :anio, :color, :vin, :tipo_motor, :observaciones, 1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':placa' => strtoupper($data['placa']),
                ':marca' => $data['marca'],
                ':modelo' => $data['modelo'],
                ':anio' => $data['anio'] ?? null,
                ':color' => $data['color'] ?? null,
                ':vin' => $data['vin'] ?? null,
                ':tipo_motor' => $data['tipo_motor'] ?? null,
                ':observaciones' => $data['observaciones'] ?? null,
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function update($data) {
        try {
            $sql = "UPDATE vehiculos SET cliente_id=:cliente_id, placa=:placa, marca=:marca, modelo=:modelo, 
                    anio=:anio, color=:color, vin=:vin, tipo_motor=:tipo_motor, observaciones=:observaciones 
                    WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cliente_id' => $data['cliente_id'],
                ':placa' => strtoupper($data['placa']),
                ':marca' => $data['marca'],
                ':modelo' => $data['modelo'],
                ':anio' => $data['anio'] ?? null,
                ':color' => $data['color'] ?? null,
                ':vin' => $data['vin'] ?? null,
                ':tipo_motor' => $data['tipo_motor'] ?? null,
                ':observaciones' => $data['observaciones'] ?? null,
                ':id' => $data['id'],
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function updateStatus($id, $estado) {
        try {
            $stmt = $this->db->prepare("UPDATE vehiculos SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (\Exception $e) { return false; }
    }

    public function getHistorialOrdenes($vehiculoId) {
        try {
            $sql = "SELECT o.*, c.nombre as cliente_nombre 
                    FROM ordenes_servicio o 
                    LEFT JOIN clientes c ON o.cliente_id = c.id 
                    WHERE o.vehiculo_id = :id 
                    ORDER BY o.fecha_recepcion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $vehiculoId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }
}
