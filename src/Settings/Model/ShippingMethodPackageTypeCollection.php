<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Support\Collection;

class ShippingMethodPackageTypeCollection extends Collection
{
    protected $cast = ShippingMethodPackageType::class;
}
