#!/bin/bash

mkdir -pm 777 ./var
mkdir -pm 777 ./var/cache
mkdir -pm 777 ./var/tmp
mkdir -pm 777 ./var/log
mkdir -pm 777 ./var/supervisor
mkdir -pm 777 ./var/php
mkdir -pm 777 ./var/nginx

echo 'APP_ENV=dev
APP_DEBUG=1
APP_SECRET='Bla$(date '+%Y%m%d%H%M%S')secret > .env.local

docker-compose up -d --build
