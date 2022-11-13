#!/bin/bash

mkdir movies series

echo Installing deps
./install.sh

cp -f config/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
service apache2 reload
