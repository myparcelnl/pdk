<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static mixed get(string $key)
 * @implements \MyParcelNL\Pdk\Base\Contract\ConfigInterface
 */
final class Config extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConfigInterface::class;
    }
}
