<?php
namespace App\Models;

use PDO;

class Usuario extends BaseModel {
    
    // Buscar usuario por email (Login)
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return null;
        }
    }

    // Buscar por ID
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, email, rol, estado FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function emailExiste($email, $excluirId = null) {
        try {
            $sql = "SELECT id FROM usuarios WHERE email = :email";
            if ($excluirId) { $sql .= " AND id != :eid"; }
            $stmt = $this->db->prepare($sql);
            $params = [':email' => $email];
            if ($excluirId) { $params[':eid'] = $excluirId; }
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_OBJ) ? true : false;
        } catch (\Exception $e) { return false; }
    }

    // --- NUEVO: Listar todos los usuarios ---
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    // --- NUEVO: Crear Usuario ---
    public function create($data) {
        try {
            // Encriptamos la contraseña antes de guardar
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (:nombre, :email, :pass, :rol, 1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':pass' => $hash,
                ':rol' => $data['rol']
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    // --- NUEVO: Actualizar Usuario ---
    public function update($data) {
        try {
            // Si viene password, lo actualizamos (encriptado). Si no, dejamos el anterior.
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre=:nombre, email=:email, password=:pass, rol=:rol WHERE id=:id";
                $params = [
                    ':nombre' => $data['nombre'], ':email' => $data['email'],
                    ':pass' => $hash, ':rol' => $data['rol'], ':id' => $data['id']
                ];
            } else {
                $sql = "UPDATE usuarios SET nombre=:nombre, email=:email, rol=:rol WHERE id=:id";
                $params = [
                    ':nombre' => $data['nombre'], ':email' => $data['email'],
                    ':rol' => $data['rol'], ':id' => $data['id']
                ];
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Exception $e) {
            return false;
        }
    }

    // --- NUEVO: Cambiar Estado (Activar/Bloquear acceso) ---
    public function updateStatus($id, $nuevoEstado) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}