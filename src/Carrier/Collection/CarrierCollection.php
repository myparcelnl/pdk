<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Carrier[] $items
 */
class CarrierCollection extends Collection
{
    protected $cast = Carrier::class;
}
