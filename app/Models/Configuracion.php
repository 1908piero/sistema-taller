<?php
namespace App\Models;

use PDO;

class Configuracion extends BaseModel {
    
    public function obtenerConfiguracion() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM configuracion LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update($data) {
        try {
            $sql = "UPDATE configuracion SET 
                    nombre_sistema = :nombre,
                    moneda = :moneda,
                    simbolo_moneda = :simbolo,
                    impuesto = :impuesto,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion,
                    terminos_orden = :terminos,    -- Nuevo
                    mensaje_ticket = :mensaje      -- Nuevo
                    ";
            
            if (!empty($data['logo'])) {
                $sql .= ", logo = :logo";
            }

            $sql .= " WHERE id = 1";

            $stmt = $this->db->prepare($sql);

            $params = [
                ':nombre' => $data['nombre_sistema'],
                ':moneda' => $data['moneda'],
                ':simbolo' => $data['simbolo_moneda'],
                ':impuesto' => $data['impuesto'],
                ':telefono' => $data['telefono'],
                ':email' => $data['email'],
                ':direccion' => $data['direccion'],
                ':terminos' => $data['terminos_orden'], // Nuevo parámetro
                ':mensaje' => $data['mensaje_ticket']   // Nuevo parámetro
            ];

            if (!empty($data['logo'])) {
                $params[':logo'] = $data['logo'];
            }

            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log("[CONFIG MODEL] update error: " . $e->getMessage());
            return false;
        }
    }
}