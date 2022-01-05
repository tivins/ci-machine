ARG PHP

FROM ubuntu:latest
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/PARIS
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# COPY script.php /script

RUN apt update ; apt install -y --no-install-recommends \
    zip unzip \
    mariadb-server \
    mariadb-client \
    libsodium-dev \
    libzip-dev \
    curl \
    rsync \
    git \
    software-properties-common

#RUN apt install apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2
RUN add-apt-repository ppa:ondrej/php
RUN apt install -y php8.1-dev php-pear

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN docker-php-ext-install pdo_mysql

CMD ls -al /run/mysqld/mysqld.sock
RUN service mariadb start

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

COPY php.ini /usr/local/etc/php/conf.d/zcover.ini

WORKDIR /box
