FROM php:8.2-fpm
RUN apt-get update && apt-get install -y \
    supervisor \
    curl \
    wget \
    zip unzip \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    librabbitmq-dev \
    libssl-dev \
    bash

RUN docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install sockets \
    && echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/php.ini \
    && echo 'xdebug.mode=debug' >> /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
USER $USER

WORKDIR /var/www
