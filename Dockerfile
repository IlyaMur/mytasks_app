FROM php:8.1.2-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y zip curl git

RUN docker-php-source extract && docker-php-ext-install pdo_mysql mysqli && docker-php-source delete

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
