#!/bin/bash

./scripts/composer.sh
./scripts/nodejs.sh

apt install -y docker \
  docker-compose \
  apache2 \
  libapache2-mod-php8.2 \
  php8.2-cli \
  php8.2-xml \
  php8.2-mbstring \
  php8.2-mysql \
  php8.2-gd \
  php8.2-curl \
  nodejs

npm i -g yarn