<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;

interface CartCalculationServiceInterface
{
    public function calculateAllowedPackageTypes(PdkCart $cart): PackageTypeCollection;

    /**
     * @return mixed
     */
    public function calculateMailboxPercentage(PdkCart $cart);

    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod;
}
