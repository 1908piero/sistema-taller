<?php
echo "<h2>Variables de entorno disponibles:</h2><pre>";
$vars = ['DB_HOST','DB_NAME','DB_USER','DB_PASS','MYSQL_URL','MYSQL_HOST','MYSQL_PORT','MYSQL_USER','MYSQL_PASSWORD','MYSQL_DATABASE'];
foreach ($vars as $v) {
    echo "$v = " . htmlspecialchars(var_export(getenv($v), true)) . "\n";
}
echo "</pre>";
