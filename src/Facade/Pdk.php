<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static mixed get(string $id)
 * @method static array getAppInfo()
 * @method static string getMode()
 * @method static bool has(string $id)
 * @method static bool isDevelopment()
 * @method static bool isProduction()
 * @implements \MyParcelNL\Pdk\Base\Pdk
 */
class Pdk extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \MyParcelNL\Pdk\Base\Pdk::class;
    }
}
