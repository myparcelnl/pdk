<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Contract;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;

interface PdkShippingMethodRepositoryInterface
{
    /**
     * Get all shipping methods.
     */
    public function all(): PdkShippingMethodCollection;
}
