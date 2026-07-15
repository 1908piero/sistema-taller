<?php
namespace App\Models;

use PDO;

class Servicio extends BaseModel {

    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM servicios ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return []; }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) { return null; }
    }

    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO servicios (nombre, descripcion, precio, categoria, duracion_estimada) 
                                        VALUES (:nombre, :descripcion, :precio, :categoria, :duracion)");
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':precio' => $data['precio'],
                ':categoria' => $data['categoria'] ?? 'general',
                ':duracion' => $data['duracion_estimada'] ?? null,
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function update($data) {
        try {
            $stmt = $this->db->prepare("UPDATE servicios SET nombre=:nombre, descripcion=:descripcion, precio=:precio, 
                                        categoria=:categoria, duracion_estimada=:duracion WHERE id=:id");
            return $stmt->execute([
                ':id' => $data['id'],
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':precio' => $data['precio'],
                ':categoria' => $data['categoria'] ?? 'general',
                ':duracion' => $data['duracion_estimada'] ?? null,
            ]);
        } catch (\Exception $e) { return false; }
    }

    public function updateStatus($id, $estado) {
        try {
            $stmt = $this->db->prepare("UPDATE servicios SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (\Exception $e) { return false; }
    }
}
