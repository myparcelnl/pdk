FROM ghcr.io/myparcelnl/php-xd:7.4-cli-alpine

# install php zip extension
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip
