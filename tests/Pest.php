<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Bootstrap\TestCase;

include __DIR__ . '/functions.php';

/**
 * Global Pest test configuration.
 *
 * @see https://pestphp.com/docs/underlying-test-case#testspestphp
 */

uses(TestCase::class)->in(__DIR__);

//uses()
//    ->afterEach(function () {
//        if (! Facade::getPdkInstance()) {
//            return;
//        }
//
//        $services = [
//            MockMemoryCacheStorage::class,
//            SharedFactoryState::class,
//        ];
//
//        foreach ($services as $service) {
//            try {
//                $instance = Pdk::get($service);
//
//                if (! $instance instanceof ResetInterface) {
//                    continue;
//                }
//
//                $instance->reset();
//            } catch (Exception $e) {
//                // Ignore
//            }
//        }
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
