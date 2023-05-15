<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Tax\Service;

use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;

abstract class AbstractTaxService implements TaxServiceInterface
{
    abstract public function getShippingDisplayPrice(float $basePrice): float;
}
