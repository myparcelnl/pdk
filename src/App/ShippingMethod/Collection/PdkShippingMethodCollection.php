<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Collection;

use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property PdkShippingMethod[] $items
 */
class PdkShippingMethodCollection extends Collection
{
    protected $cast = PdkShippingMethod::class;
}
