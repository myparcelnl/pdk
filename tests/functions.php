<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\FactoryFactory;

function mockPdkProperty(string $property, $value): callable
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $mockPdk */
    $mockPdk = Pdk::get(PdkInterface::class);

    $oldValue = $mockPdk->get($property);

    $mockPdk->set($property, $value);

    return static function () use ($mockPdk, $oldValue, $property) {
        $mockPdk->set($property, $oldValue);
    };
}

function mockPlatform(string $platform): callable
{
    return mockPdkProperty('platform', $platform);
}

/**
 * @param  class-string $class
 * @param  mixed        ...$args
 */
function factory(string $class, ...$args)
{
    return FactoryFactory::create($class, ...$args);
}
