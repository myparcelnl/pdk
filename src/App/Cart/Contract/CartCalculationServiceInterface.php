<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;

interface CartCalculationServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection
     */
    public function calculateAllowedPackageTypes(PdkCart $cart): PackageTypeCollection;

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return mixed
     */
    public function calculateMailboxPercentage(PdkCart $cart);

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod
     */
    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod;
}
