#!/bin/bash
set -e

# Ejecutar migracion de base de datos automaticamente
php /var/www/html/migracion.php 2>&1 || true

exec "$@"
