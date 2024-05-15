<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withCache(__DIR__ . '/.cache/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/private',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // TODO: Configure setlists
    ->withSets([])
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    /**
     * Global overrides.
     */
    ->withSkip([
        /**
         * Replaces string interpolation with sprintf calls. This is not always desirable. From SetList::CODING_STYLE.
         */
        EncapsedStringsToSprintfRector::class,
    ])
    /**
     * Overrides for tests.
     */
    ->withSkip([
        StaticArrowFunctionRector::class => [__DIR__ . '/tests'],
        StaticClosureRector::class       => [__DIR__ . '/tests'],
    ]);

