<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static void install(...$args)
 * @method static void uninstall(...$args)
 * @see InstallerServiceInterface
 */
final class Installer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InstallerServiceInterface::class;
    }
}
