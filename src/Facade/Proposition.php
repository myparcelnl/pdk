<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Proposition\PropositionManager;

/**
 * Facade for proposition-specific operations
 * This is the new preferred way to access proposition configuration.
 * For legacy code, Platform facade is still available.
 *
 * @method static array all()
 * @method static mixed get(string $key)
 * @method static CarrierCollection getCarriers()
 * @method static string getPropositionName()
 *
 * @see \MyParcelNL\Pdk\Proposition\PropositionManager
 */
final class Proposition extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PropositionManager::class;
    }
}
