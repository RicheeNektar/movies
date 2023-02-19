#!/bin/bash

./scripts/composer.sh
./scripts/nodejs.sh

apt install -y docker \
  docker-compose \
  apache2 \
  libapache2-mod-php8.1 \
  php8.1-cli \
  php8.1-xml \
  php8.1-mbstring \
  php8.1-mysql \
  php8.1-gd \
  php8.1-curl \
  nodejs

npm i -g yarn