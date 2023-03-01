<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

interface TaxServiceInterface
{
    /**
     * @param  float $basePrice price as number excluding tax, irrelevant of currency or cents
     *
     * @return float the price including applicable taxes
     */
    public function getShippingDisplayPrice(float $basePrice): float;
}
