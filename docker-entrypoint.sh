#!/bin/bash
set -e

# Verificar que vendor/ exista (Railway a veces no preserva las capas de build de Docker)
if [ ! -d /var/www/html/vendor ]; then
    cd /var/www/html && composer install --no-interaction --prefer-dist --no-dev
fi

# Ejecutar migracion de base de datos automaticamente
php /var/www/html/migracion.php 2>&1 || true

exec "$@"
