<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

// TODO: Configure setlists
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory(__DIR__ . '/.cache/rector');

    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/private',
        __DIR__ . '/src',
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

    $rectorConfig->rules([
        DeclareStrictTypesRector::class,
    ]);

    $rectorConfig->rule(DeclareStrictTypesRector::class);

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
     * Overrides for tests.
     */
    $rectorConfig
        ->skip([
            StaticArrowFunctionRector::class => [__DIR__ . '/tests'],
            StaticClosureRector::class       => [__DIR__ . '/tests'],
        ]);
};
