<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkCart[] $items
 */
class PdkShippingMethodCollection extends Collection
{
    protected $cast = PdkShippingMethod::class;
}
