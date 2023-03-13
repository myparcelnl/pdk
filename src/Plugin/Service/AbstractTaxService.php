<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Contract\TaxServiceInterface;

abstract class AbstractTaxService implements TaxServiceInterface
{
    abstract public function getShippingDisplayPrice(float $basePrice): float;
}
