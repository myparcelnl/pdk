<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Bootstrap\TestCase;

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

uses(TestCase::class)->in(__DIR__);

uses()
    ->group('frontend')
    ->in(
        __DIR__ . '/Unit/App/Action',
        __DIR__ . '/Unit/Context',
        __DIR__ . '/Unit/Frontend'
    );

uses()
    ->group('settings')
    ->in(
        __DIR__ . '/Unit/App/Action/Backend/Settings',
        __DIR__ . '/Unit/Frontend/View',
        __DIR__ . '/Unit/Settings'
    );

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
