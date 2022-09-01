<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Sdk\src\Support\Arr;

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

uses()
    ->afterEach(function () {
        // Reset Facade after each test.
        Facade::setPdkInstance(null);
    })
    ->in(__DIR__);

uses()
    ->group('model')
    ->in(__DIR__ . '/Unit/Base/Model');

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
