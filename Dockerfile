FROM php:8.0-apache

RUN apt-get update && \
    apt-get upgrade -y

RUN apt-get install -y \
        libicu-dev \
        libpng-dev \
        libwebp-dev \
        libjpeg-dev \
        g++ \
        cron

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-jpeg --with-webp

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    gd

COPY /config/docker/vhost.conf /etc/apache2/sites-available/vhost.conf
COPY /config/docker/crontab /richee.movie.dos.cron

RUN sed -e "s/\r//g" /richee.movie.dos.cron > /richee.movie.cron

ADD /config/docker/entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT /entrypoint.sh
