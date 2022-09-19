FROM php:8.0-apache

COPY /config/docker/vhost.conf /etc/apache2/sites-available/vhost.conf

COPY /config/docker/cron /etc/cron.d/cron

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        libicu-dev \
        libpng-dev \
        libwebp-dev \
        libjpeg-dev \
        g++

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-jpeg --with-webp

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    gd

RUN a2dissite 000-default.conf && a2ensite vhost.conf;
