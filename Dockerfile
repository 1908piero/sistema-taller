FROM php:8.2-apache

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install intl gd && \
    apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --no-dev || true

RUN mkdir -p public/uploads/logo public/uploads/productos && \
    chmod -R 777 public/uploads

# Configurar PHP para permitir subida de imágenes grandes
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "" > /etc/apache2/mods-enabled/mpm_event.load 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_worker.load 2>/dev/null; \
    echo "" > /etc/apache2/mods-enabled/mpm_worker.conf 2>/dev/null

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
