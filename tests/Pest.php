<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Bootstrap\TestCase;

include __DIR__ . '/usesShared.php';
include __DIR__ . '/functions.php';

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

uses(TestCase::class)->in(__DIR__);

//uses()
//    ->afterEach(function () {
//        /** @noinspection ForgottenDebugOutputInspection */
//        error_log(
//            sprintf(
//                '%s Memory usage: %d MB out of %d MB',
//                str_pad(Utils::classBasename(get_class($this)) . ']', 50),
//                memory_get_usage(true) / 1024 / 1024,
//                memory_get_peak_usage(true) / 1024 / 1024
//            )
//        );
//    })
//    ->in(__DIR__);

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
