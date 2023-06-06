<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Contract;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;

interface PdkShippingMethodRepositoryInterface
{
    /**
     * Get all shipping methods.
     */
    public function all(): PdkShippingMethodCollection;

    /**
     * Get a shipping method.
     */
    public function get($input): PdkShippingMethod;

    /**
     * Get multiple shipping methods.
     */
    public function getMany($input): PdkShippingMethodCollection;
}
