#!/bin/bash

crontab ./config/docker/cron/crontab
cp ./config/docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

service cron start

apache2ctl -DFOREGROUND
