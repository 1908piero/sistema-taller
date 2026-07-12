<?php
namespace App\Models;

use PDO;

class Gasto extends BaseModel {
    
    // Listar todos los gastos ordenados por fecha
    public function getAll() {
        try {
            $sql = "SELECT g.*, u.nombre as usuario_nombre 
                    FROM gastos g
                    LEFT JOIN usuarios u ON g.usuario_id = u.id
                    ORDER BY g.fecha DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Registrar nuevo gasto
    public function create($data) {
        try {
            $sql = "INSERT INTO gastos (descripcion, categoria, monto, usuario_id, fecha) 
                    VALUES (:desc, :cat, :monto, :user, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':desc' => $data['descripcion'],
                ':cat' => $data['categoria'],
                ':monto' => $data['monto'],
                ':user' => $_SESSION['user_id'] ?? 1
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Eliminar gasto
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM gastos WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}