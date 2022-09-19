#!/bin/bash

composer install

yarn

yarn build

bin/console app:images:convert-local