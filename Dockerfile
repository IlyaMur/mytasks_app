FROM php:8.1.2-apache

RUN a2enmod rewrite
RUN apt-get update
RUN apt-get update && apt-get install -y zip

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-source extract && docker-php-ext-install pdo_mysql mysqli && docker-php-source delete

RUN apt-get update && apt-get install -y curl git
