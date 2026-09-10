FROM php:8.2-apache

# Instalar extensiones PDO MySQL y habilitar mod_rewrite
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

WORKDIR /var/www/html
