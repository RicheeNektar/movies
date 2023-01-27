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

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && \
    apt-get install nodejs

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-jpeg --with-webp

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    gd

RUN npm install -g yarn

ENTRYPOINT ["/var/www/html/config/docker/entrypoint.sh"]
