<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

abstract class AbstractTaxService implements TaxServiceInterface
{
    abstract public function getShippingDisplayPrice(float $basePrice): float;
}
