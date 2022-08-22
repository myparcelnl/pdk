<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static get(string $id)
 * @method static has(string $id)
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
