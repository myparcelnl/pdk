<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;

interface DeliveryOptionsServiceInterface
{
    /**
     * Creates an array with the packageType and carrierSettings key of the delivery options config.
     */
    public function createAllCarrierSettings(PdkCart $cart): array;

    /**
     * Creates an array with the delivery options platformSettings config based on the active proposition.
     * @return array
     */
    public function createPropositionConfig(): array;
}
