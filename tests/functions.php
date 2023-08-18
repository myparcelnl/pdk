<?php
/** @noinspection PhpDocMissingThrowsInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests;

use MyParcelNL\Pdk\Tests\Bootstrap\Facade\Mock;
use MyParcelNL\Pdk\Tests\Factory\FactoryFactory;

function mockPdkProperty(string $property, $value): void
{
    Mock::override($property, $value);
}

function mockPdkProperties(array $properties): void
{
    Mock::overrideMany($properties);
}

/**
 * @param  class-string $class
 * @param  mixed        ...$args
 */
function factory(string $class, ...$args)
{
    return FactoryFactory::create($class, ...$args);
}
