
# Get PHP-version from build-args
ARG PHP

# New image from official php images
FROM php:${PHP}-fpm

# Update and install required for later installs
RUN apt-get update && apt install -y --no-install-recommends \
    libzip-dev zip unzip git libmagickwand-dev libonig-dev

# Install php modules
RUN pecl install xdebug imagick \
    && docker-php-ext-configure intl \
    && docker-php-ext-enable xdebug imagick \
    && docker-php-ext-install pdo_mysql mbstring zip gd intl

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Setup xdebug for code coverage
RUN echo "xdebug.mode=coverage" > /usr/local/etc/php/conf.d/xdebug.ini

# Copy our test script
COPY test.php /test.php

# Create/log user (UID/GID:1000)
RUN useradd -ms /bin/bash admin && echo "admin:admin" | chpasswd
USER admin

# Default working directory
# @see docker -w, --workdir
WORKDIR /box
