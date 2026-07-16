<?php
/**
 * RNF-09: Script de backup automático diario
 * 
 * Uso:
 *   php backup.php                          # Backup manual
 *   php backup.php --restore archivo.sql    # Restaurar desde backup
 * 
 * Para programar backup diario en Railway:
 *   Agregar un Cron Job que ejecute: php backup.php
 * 
 * Para Windows Task Scheduler:
 *   Programar ejecución diaria de: php D:\ruta\backup.php
 */

$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'taller_db';
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: '3306';

if ($url = getenv('MYSQL_URL')) {
    $parts = parse_url($url);
    $host = $parts['host'] ?? $host;
    $user = $parts['user'] ?? $user;
    $pass = $parts['pass'] ?? $pass;
    $dbname = ltrim($parts['path'] ?? '', '/') ?: $dbname;
    $port = $parts['port'] ?? $port;
}

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Retención: mantener últimos 30 días
$retencion = 30;

// Modo restauración
if (isset($argv[1]) && $argv[1] === '--restore') {
    $archivo = $argv[2] ?? null;
    if (!$archivo || !file_exists($archivo)) {
        die("Uso: php backup.php --restore <archivo.sql>\n");
    }
    $cmd = sprintf('mysql -h%s -P%s -u%s -p%s %s < %s', $host, $port, $user, $pass, $dbname, $archivo);
    exec($cmd, $output, $exitCode);
    if ($exitCode === 0) {
        echo "Restauración exitosa desde: $archivo\n";
    } else {
        echo "Error en restauración.\n";
    }
    exit;
}

// Backup
$fecha = date('Y-m-d_H-i-s');
$archivo = "$backupDir/backup_{$fecha}.sql";

$cmd = sprintf('mysqldump -h%s -P%s -u%s -p%s --routines --triggers --single-transaction %s > %s', $host, $port, $user, $pass, $dbname, $archivo);
exec($cmd, $output, $exitCode);

if ($exitCode === 0) {
    echo "Backup creado: $archivo\n";

    // Limpiar backups antiguos (retención 30 días)
    $archivos = glob("$backupDir/backup_*.sql");
    usort($archivos, function($a, $b) { return filemtime($a) - filemtime($b); });
    $total = count($archivos);
    if ($total > $retencion) {
        $eliminar = array_slice($archivos, 0, $total - $retencion);
        foreach ($eliminar as $e) {
            unlink($e);
            echo "  Eliminado backup antiguo: " . basename($e) . "\n";
        }
    }
} else {
    echo "Error al crear backup.\n";
    exit(1);
}
