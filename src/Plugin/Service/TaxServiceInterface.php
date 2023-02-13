<?php

namespace MyParcelNL\Pdk\Plugin\Service;

interface TaxServiceInterface
{
    /**
     * @param  float $value price as number excluding tax, irrelevant of currency or cents
     *
     * @return float the price including applicable taxes
     */
    public function getShippingDisplayPrice(float $value): float;
}
