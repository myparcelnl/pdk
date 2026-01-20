<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Platform\PlatformManager;

/**
 * Facade for platform-specific operations.
 *
 * @deprecated Use PropositionService instead. This facade is maintained for backward compatibility.
 * @see \MyParcelNL\Pdk\Proposition\Service\PropositionService
 *
 * @method static array all()
 * @method static mixed get(string $key)
 * @method static CarrierCollection getCarriers()
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
