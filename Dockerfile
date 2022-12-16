FROM php:8.0-apache

RUN apt-get update && \
    apt-get upgrade -y

RUN apt-get install -y \
    libicu-dev \
    libpng-dev \
    libwebp-dev \
    libjpeg-dev \
    g++ \
    cron \
    unzip

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-jpeg --with-webp

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    gd

ENTRYPOINT ["/var/www/html/config/docker/entrypoint.sh"]
