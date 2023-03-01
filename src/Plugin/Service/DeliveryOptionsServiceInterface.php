<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;

interface DeliveryOptionsServiceInterface
{
    /**
     * Create all carrier settings for the given cart. The settings are used to create the delivery options config.
     */
    public function createAllCarrierSettings(PdkCart $cart): array;
}
