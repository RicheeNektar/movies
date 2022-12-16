#!/bin/bash

crontab /config/cron/crontab
cp /config/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

service cron start

apache2ctl -DFOREGROUND
