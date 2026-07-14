<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== DIAGNÓSTICO DE SUBIDA DE IMÁGENES ===\n\n";

echo "--- DIRECTORIOS ---\n";
$dirs = [
    'public/uploads/productos/' => __DIR__ . '/uploads/productos/',
    'public/uploads/logo/' => __DIR__ . '/uploads/logo/',
];
foreach ($dirs as $name => $path) {
    echo "$name:\n";
    echo "  Ruta real: $path\n";
    echo "  Existe: " . (file_exists($path) ? 'SI' : 'NO') . "\n";
    if (file_exists($path)) {
        echo "  Escribible: " . (is_writable($path) ? 'SI' : 'NO') . "\n";
        echo "  Permisos: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
    }
    echo "\n";
}

echo "\n--- PHP INFO ---\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";

echo "\n--- EXTENSIONES ---\n";
echo "gd: " . (extension_loaded('gd') ? 'SI' : 'NO') . "\n";
echo "pdo_mysql: " . (extension_loaded('pdo_mysql') ? 'SI' : 'NO') . "\n";
echo "fileinfo: " . (extension_loaded('fileinfo') ? 'SI' : 'NO') . "\n";

echo "\n--- SESIÓN ---\n";
session_start();
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO LOGUEADO') . "\n";

echo "\n--- PRUEBA DE ESCRITURA ---\n";
$testDir = __DIR__ . '/uploads/productos/';
if (!file_exists($testDir)) {
    mkdir($testDir, 0777, true);
    echo "Directorio creado: " . (file_exists($testDir) ? 'SI' : 'NO') . "\n";
}
$testFile = $testDir . 'test_' . time() . '.txt';
$written = file_put_contents($testFile, 'test');
echo "Archivo de prueba escrito: " . ($written !== false ? "SI ($written bytes)" : 'NO') . "\n";
if ($written !== false) {
    unlink($testFile);
    echo "Archivo de prueba eliminado: SI\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
