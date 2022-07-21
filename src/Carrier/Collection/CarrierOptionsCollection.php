<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Carrier\Model\Options\PackageType;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Options\PackageType[] $items
 */
class CarrierOptionsCollection extends Collection
{
    protected $cast = PackageType::class;
}
