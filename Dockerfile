FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/uploads/ \
    && chmod -R 755 /var/www/html/application/cache/

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8080
