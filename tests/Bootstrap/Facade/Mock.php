<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Tests\Bootstrap\Contract\MockPdkServiceInterface;

/**
 * @method static void override(string $key, $value)
 * @method static void overrideMany(array $config)
 * @method static void reset()
 * @implements MockPdkServiceInterface
 */
final class Mock extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MockPdkServiceInterface::class;
    }
}
