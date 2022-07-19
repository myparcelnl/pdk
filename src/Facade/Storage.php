<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static void delete(string $storageKey)
 * @method static mixed get(string $storageKey)
 * @method static bool has(string $storageKey)
 * @method static void set(string $storageKey, $item)
 * @implements \MyParcelNL\Pdk\Storage\StorageInterface
 */
class Storage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'storage.default';
    }
}
