ARG PHP_VERSION=7.4

###
# Test
###
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION} AS test

COPY composer.json phpunit.xml ./

RUN composer install --dev

CMD ["vendor/bin/pest"]


###
# Dev
###
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION} AS dev

RUN version="$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;")" \
    && architecture="$(uname -m)" \
    && mkdir -p /tmp/blackfire \
    # Install Blackfire client
    && curl -A "Docker" -L "https://blackfire.io/api/v1/releases/cli/linux/$architecture" | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    # Install Blackfire probe
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s "https://blackfire.io/api/v1/releases/probe/php/alpine/$architecture/$version" \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so "$(php -r "echo ini_get ('extension_dir');")/blackfire.so" \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > "$PHP_INI_DIR/conf.d/blackfire.ini" \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

CMD ["sh", "-c", "composer update"]
