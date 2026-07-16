<?php
/**
 * Migración: Renombrar rol 'Mecánico' → 'Técnico'
 * Para alinear con Prototipo 06 del documento de requisitos.
 */

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'sistema_taller';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Actualizando usuarios con rol 'Mecánico' → 'Técnico'...\n";
    $stmt = $pdo->prepare("UPDATE usuarios SET rol = 'Técnico' WHERE rol = 'Mecánico'");
    $stmt->execute();
    echo "Filas afectadas: " . $stmt->rowCount() . "\n";

    echo "Alterando ENUM de la columna rol...\n";
    $pdo->exec("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('Jefe','Admin','Recepcionista','Técnico') DEFAULT 'Recepcionista'");
    echo "Columna rol actualizada correctamente.\n";

    echo "Migración completada exitosamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
