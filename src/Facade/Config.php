<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static get(string $key): mixed
 * @implements \MyParcelNL\Pdk\Base\Contract\ConfigInterface
 */
class Config extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MyParcelNL\Pdk\Base\Config::class;
    }
}
