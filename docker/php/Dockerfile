FROM php:8.0-fpm-alpine

 # Apk install
RUN apk --no-cache update && apk --no-cache add bash git
RUN apk add --no-cache zip libzip-dev

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

RUN php /var/www/composer.phar install