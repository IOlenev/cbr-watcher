#!/bin/bash

mkdir -m 777 ./var
mkdir -m 777 ./var/tmp
mkdir -m 777 ./var/log
mkdir -m 777 ./var/php

docker-compose up -d --build