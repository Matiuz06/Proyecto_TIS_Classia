FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf '<Directory /var/www/html/storage/private>\n    Require all denied\n</Directory>\n<Directory /var/www/html/assets/uploads/cursos>\n    Require all denied\n</Directory>\n<Directory /var/www/html/assets/uploads/solicitudes>\n    Require all denied\n</Directory>\n' > /etc/apache2/conf-available/classia-private.conf \
    && a2enconf classia-private

WORKDIR /var/www/html
