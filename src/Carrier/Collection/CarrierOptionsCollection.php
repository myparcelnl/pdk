<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\CarrierOptions[] $items
 */
class CarrierOptionsCollection extends Collection
{
    protected $cast = CarrierOptions::class;
}
