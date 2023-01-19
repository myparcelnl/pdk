<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Plugin\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;

interface PdkShippingMethodRepositoryInterface
{
    public function get($input): PdkShippingMethod;

    public function getMany($input): PdkShippingMethodCollection;
}
