<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @method static CarrierCollection carrierCollectionToLegacyFormat(CarrierCollection $carriers)
 * @method static Carrier convertCarrierToLegacyFormat(Carrier $carrier)
 * @method static string getLegacyIdentifier(string $externalIdentifier)
 * @method static array getLegacyPackageTypes()
 * @method static array getLegacyDeliveryTypes()
 * @method static array getLegacyShipmentOptions()
 * @see \MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface
 */
final class FrontendData extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return FrontendDataAdapterInterface::class;
    }
}
