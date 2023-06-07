<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Platform\PlatformManager;

/**
 * @method static array all()
 * @method static mixed get(string $key)
 * @method static string getPlatform()
 * @implements \MyParcelNL\Pdk\Platform\PlatformManager
 */
final class Platform extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PlatformManager::class;
    }
}
