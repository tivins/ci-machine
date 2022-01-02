ARG PHP
FROM php:${PHP}-fpm
RUN php --version

# COPY script.php /script

RUN apt update -y ; \
    apt install -y \
      zip unzip \
      libsodium-dev \
      libzip-dev \
      curl \
      rsync \
      git

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN docker-php-ext-install pdo_mysql

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

COPY php.ini /usr/local/etc/php/conf.d/zcover.ini

WORKDIR /box
