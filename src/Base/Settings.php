<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

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
