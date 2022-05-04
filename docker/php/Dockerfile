FROM php:8.0-fpm-alpine
WORKDIR /var/www/
RUN apk add --no-cache zip libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip