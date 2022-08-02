<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @method static get(string $key): mixed
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
