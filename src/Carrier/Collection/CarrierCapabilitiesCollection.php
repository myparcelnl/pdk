<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities[] $items
 */
class CarrierCapabilitiesCollection extends Collection
{
    protected $cast = CarrierCapabilities::class;
}
