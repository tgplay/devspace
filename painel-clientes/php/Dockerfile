FROM php:8.2-fpm-alpine

RUN apk add --no-cache postgresql-dev libzip-dev icu-dev zip unzip

RUN docker-php-ext-install pdo pdo_pgsql pgsql zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/painel
