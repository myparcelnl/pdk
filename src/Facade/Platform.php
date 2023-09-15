<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Platform\PlatformManager;

/**
 * @method static array all()
 * @method static mixed get(string $key)
 * @method static CarrierCollection getCarriers()
 * @method static string getPlatform()
 * @see \MyParcelNL\Pdk\Platform\PlatformManager
 */
final class Platform extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PlatformManager::class;
    }
}
