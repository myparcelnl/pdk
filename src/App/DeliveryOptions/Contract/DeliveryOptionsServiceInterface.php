<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

interface DeliveryOptionsServiceInterface
{
    /**
     * Creates an array with the packageType and carrierSettings key of the delivery options config.
     */
    public function createAllCarrierSettings(PdkCart $cart): array;

    /**
     * @param  int                                        $weight
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType $packageType
     *
     * @return int
     */
    public function getWeightByPackageType(int $weight, PackageType $packageType): int;
}
