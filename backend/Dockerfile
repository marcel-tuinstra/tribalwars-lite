FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev && \
    docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
