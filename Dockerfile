ARG PHP_VERSION=7.4
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION}

# install php zip extension
RUN apt-get update \
    && apt-get install -y --no-install-recommends php${PHP_VERSION}-zip \
    && rm -rf /var/lib/apt/lists/*

# Increase PHP memory limit
RUN echo "memory_limit = 512M" > "${PHP_INI_DIR}/mods-available/memory-limit.ini" \
    && phpenmod memory-limit
