<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;

/**
 * Optional extension for cart calculators that can distinguish unknown weight.
 * Existing CartCalculationServiceInterface implementations remain compatible.
 */
interface KnownCartWeightServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                  $packageTypeName
     *
     * @return null|int Known shipping weight in grams, including packaging once.
     */
    public function getKnownCartWeightForPackageType(PdkCart $cart, string $packageTypeName): ?int;
}
