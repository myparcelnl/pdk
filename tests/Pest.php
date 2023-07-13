<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Uses\ClearContainerCache;
use function MyParcelNL\Pdk\Tests\usesShared;

include __DIR__ . '/usesShared.php';

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

usesShared(new ClearContainerCache())->in(__DIR__);

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
