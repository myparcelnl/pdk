FROM ghcr.io/myparcelnl/php-xd:7.4-fpm-alpine

# install php zip extension
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip \
    && apk del libzip-dev
