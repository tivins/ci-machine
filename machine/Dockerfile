ARG PHP

FROM php:${PHP}-fpm

RUN apt-get update && apt install -y --no-install-recommends \
    libzip-dev zip unzip git libmagickwand-dev libonig-dev

RUN pecl install xdebug imagick \
    && docker-php-ext-enable xdebug imagick \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version


RUN echo "xdebug.mode=coverage" > /usr/local/etc/php/conf.d/xdebug.ini
COPY test.php /test.php

# Create user (UID/GID:1000)
RUN useradd -ms /bin/bash admin && echo "admin:admin" | chpasswd
USER admin

WORKDIR /box