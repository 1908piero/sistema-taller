<?php
namespace App\Models;

use Config\Database;
use PDO;

class BaseModel {
    protected $db;

    public function __construct() {
        // Instancia la conexión automáticamente para cualquier modelo hijo
        $database = new Database();
        $this->db = $database->getConnection();
    }
}