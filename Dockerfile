ARG PHP_VERSION=7.4

###
# Test
###
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION} AS test

COPY composer.json phpunit.xml ./
COPY tests/        ./tests/
COPY src/          ./src/

RUN composer install --dev

CMD ["vendor/bin/pest", "--coverage-clover", "coverage.xml"]


###
# Dev
###
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION} AS dev

CMD ["sleep", "infinity"]
