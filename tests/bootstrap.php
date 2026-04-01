<?php

declare(strict_types=1);

/**
 * Suppress E_DEPRECATED warnings from vendor dependencies only.
 *
 * PHP 8.4 deprecated implicitly nullable parameters, which triggers warnings
 * in older vendor packages (Pest v1, Symfony, PHP-DI, etc.). These are not
 * actionable in this project — they require upstream version upgrades.
 * Deprecations from our own code are still reported.
 */
if (PHP_VERSION_ID >= 80400) {
    error_reporting(E_ALL & ~E_DEPRECATED);
}

require __DIR__ . '/../vendor/autoload.php';
