<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Contract;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;

interface PdkShippingMethodRepositoryInterface
{
    public function all(): PdkShippingMethodCollection;

    public function get($input): PdkShippingMethod;

    public function getMany($input): PdkShippingMethodCollection;
}
