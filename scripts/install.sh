#!/bin/bash

mkdir movies/ series/ var/
chmod 777 var/

yarn install

composer install

a2enmod proxy
cp -f config/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
service apache2 reload