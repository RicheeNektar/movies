#!/bin/bash

mkdir /var/log/apache2/

a2dissite 000-default.conf && a2ensite vhost.conf;

crontab /richee.movie.cron
service cron start

apache2ctl -DFOREGROUND
