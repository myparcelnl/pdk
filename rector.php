<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\Config\RectorConfig;

// TODO: Configure setlists
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory(__DIR__ . '/.cache/rector');

    $rectorConfig->paths([
        __DIR__ . '/helper',
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        // LevelSetList::UP_TO_PHP_71,
        // SetList::CODE_QUALITY,
        // SetList::CODING_STYLE,
        // SetList::DEAD_CODE,
        // SetList::EARLY_RETURN,
        // SetList::INSTANCEOF,
        // SetList::PRIVATIZATION,
        // SetList::TYPE_DECLARATION,
    ]);

    $rectorConfig->ruleWithConfiguration(ConsistentPregDelimiterRector::class, [
        ConsistentPregDelimiterRector::DELIMITER => '/',
    ]);

    /**
     * Global overrides.
     */
    $rectorConfig->skip([
        /**
         * Replaces string interpolation with sprintf calls. This is not always desirable. From SetList::CODING_STYLE.
         */
        EncapsedStringsToSprintfRector::class,
    ]);

    /**
     * Overrides for Pest tests.
     */
    $rectorConfig
        ->withPath(__DIR__ . '/tests')
        ->skip([
            StaticArrowFunctionRector::class,
            StaticClosureRector::class,
        ]);
};
