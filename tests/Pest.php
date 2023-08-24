<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Factory\SharedFactoryState;
use MyParcelNL\Pdk\Tests\Uses\ClearContainerCache;
use Symfony\Contracts\Service\ResetInterface;
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

uses()
    ->group('frontend')
    ->in(__DIR__ . '/Unit/Frontend');

uses()
    ->group('settings')
    ->in(__DIR__ . '/Unit/Settings');

uses()
    ->afterEach(function () {
        if (! Facade::getPdkInstance()) {
            return;
        }

        $services = [
            MockMemoryCacheStorage::class,
            SharedFactoryState::class,
        ];

        foreach ($services as $service) {
            try {
                $instance = Pdk::get($service);

                if (! $instance instanceof ResetInterface) {
                    continue;
                }

                $instance->reset();
            } catch (Exception $e) {
                // Ignore
            }
        }
    })
    ->in(__DIR__);

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
