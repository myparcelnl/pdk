<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Settings\SettingsManager;

/**
 * @method static mixed get(string $key)
 * @method static \MyParcelNL\Pdk\Settings\Model\Settings all()
 * @method static void persist()
 * @implements \MyParcelNL\Pdk\Settings\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
