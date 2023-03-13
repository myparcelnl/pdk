<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;

interface DeliveryOptionsServiceInterface
{
    /**
     * Creates an array with the packageType and carrierSettings key of the delivery options config.
     */
    public function createAllCarrierSettings(PdkCart $cart): array;
}
