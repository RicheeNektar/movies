FROM php:8.0-apache

COPY /config/docker/vhost.conf /etc/apache2/sites-available/vhost.conf

COPY /config/docker/cron /etc/cron.d/cron

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        libicu-dev \
        g++

RUN docker-php-ext-configure intl

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl

RUN a2dissite 000-default.conf && a2ensite vhost.conf;
