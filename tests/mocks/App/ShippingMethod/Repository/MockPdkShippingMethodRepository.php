<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Repository;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;

final class MockPdkShippingMethodRepository extends AbstractPdkShippingMethodRepository
{
    public function all(): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection();
    }

    public function get($input): PdkShippingMethod
    {
        return new PdkShippingMethod();
    }
}
