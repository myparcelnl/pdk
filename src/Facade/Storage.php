<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageStackInterface;

/**
 * @method static StorageDriverInterface layer(null|string $layer = null)
 */
final class Storage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StorageStackInterface::class;
    }
}
