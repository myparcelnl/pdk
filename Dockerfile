ARG PHP_VERSION=7.4
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION}

# install php zip extension
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip

# Increase PHP memory limit
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini
