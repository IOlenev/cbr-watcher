FROM php:8.2-fpm
RUN apt-get update && apt-get install -y \
	curl \
    wget \
    zip unzip \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    bash

RUN docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
