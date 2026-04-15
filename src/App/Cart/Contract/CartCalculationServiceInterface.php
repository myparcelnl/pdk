<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;

interface CartCalculationServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return float
     */
    public function calculateMailboxPercentage(PdkCart $cart);

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod
     */
    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod;

    /**
     * Get the unique package types requested by products in the cart.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return string[] PDK package type names
     */
    public function getCartPackageTypes(PdkCart $cart): array;

    /**
     * Calculate the total cart weight including empty package weight for the given package type.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                  $packageTypeName
     *
     * @return int
     */
    public function getCartWeightForPackageType(PdkCart $cart, string $packageTypeName): int;
}
