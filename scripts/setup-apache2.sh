#!/bin/bash

cp -f config/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
service apache2 reload
