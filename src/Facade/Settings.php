<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;

/**
 * @method static mixed get(string $key, string $namespace = null, $default = null)
 * @method static \MyParcelNL\Pdk\Settings\Model\Settings all()
 * @method static void persist()
 * @method static array getDefaults()
 * @implements \MyParcelNL\Pdk\Settings\SettingsManager
 */
final class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManagerInterface::class;
    }
}
