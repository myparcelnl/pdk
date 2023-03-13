<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;

interface CartCalculationServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     */
    public function calculateAllowedPackageTypes(PdkCart $cart): array;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return mixed
     */
    public function calculateMailboxPercentage(PdkCart $cart);

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod
     */
    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod;
}
