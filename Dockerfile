FROM php:8.2-apache

# 1. WORKDIR
WORKDIR /var/www/html

# 2. Dependencias del sistema + extensiones PHP
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libxml2-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install intl gd zip xml mbstring pdo pdo_mysql && \
    apt-get clean

# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Copiar solo archivos de dependencias
COPY composer.json composer.lock ./

# 5. Instalar dependencias (sin autoloader aún)
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# 6. Copiar el resto de la aplicación
COPY . .

# 7. Generar autoloader optimizado
RUN composer dump-autoload --optimize

# 8. Permisos
RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p public/uploads/logo public/uploads/productos && \
    chmod -R 777 public/uploads

# Configurar PHP para subida de imágenes grandes
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Apache config
RUN a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "" > /etc/apache2/mods-enabled/mpm_event.load 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_worker.load 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_worker.conf 2>/dev/null

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
