ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-cli AS base

ARG DEBIAN_NONINTERACTIVE=1
ARG XDEBUG_VERSION="3.1.3"

WORKDIR /app

RUN apt-get update && \
    apt-get --yes install \
        libzip-dev \
        fswatch \
        zip unzip && \
    docker-php-ext-install zip && \
    docker-php-ext-enable zip && \
    curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/bin \
        --filename=composer && \
    chmod +x /usr/bin/composer && \
    pecl install xdebug-${XDEBUG_VERSION} && \
    docker-php-ext-enable xdebug && \
    touch /usr/local/etc/php/conf.d/zzz-xdebug.ini && \
    echo 'xdebug.mode = coverage' >> /usr/local/etc/php/conf.d/zzz-xdebug.ini && \
    echo 'xdebug.client_host = host.docker.internal' >> /usr/local/etc/php/conf.d/zzz-xdebug.ini


###
# Test
###
FROM base AS test

COPY composer.json composer.lock ./

RUN composer install

COPY src         ./src
COPY tests       ./tests
COPY phpunit.xml ./

CMD ["vendor/bin/pest", "--coverage-clover", "coverage.xml"]


###
# Dev
###
FROM base AS dev

CMD ["sleep", "infinity"]
