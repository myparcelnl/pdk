<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main PDK facade. This facade is used to access the PDK instance and service container.
 * @method static mixed get(string $id)
 * @method static string getMode()
 * @method static bool has(string $id)
 * @method static bool isDevelopment()
 * @method static bool isProduction()
 * @method static Response execute(string $id, array $params = [])
 *
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
