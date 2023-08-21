<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\ClearContainerCache;
use function MyParcelNL\Pdk\Tests\usesShared;

include __DIR__ . '/usesShared.php';
include __DIR__ . '/functions.php';

const TESTS_DIR = __DIR__;
const ROOT_DIR  = TESTS_DIR . '/..';

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

usesShared(new ClearContainerCache())->in(__DIR__);

uses()->afterEach(static function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage $storage */
    $storage = Pdk::get(StorageInterface::class);

    $storage->reset();
});

expect()
    ->extend('toHaveKeysAndValues', function (array $array) {
        $this->value = Arr::only($this->value, array_keys($array));

        return $this->toEqual($array);
    });

expect()
    ->extend('toEqualIgnoringNull', function (array $array) {
        $this->value = array_filter($this->value, static function ($item) { return null !== $item; });

        return $this->toEqual($array);
    });
