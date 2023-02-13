<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

abstract class TaxService implements TaxServiceInterface
{
    abstract public function getShippingDisplayPrice(float $basePrice): float;
}
