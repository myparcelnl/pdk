<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\App\ShippingMethod\Repository\AbstractPdkShippingMethodRepository;

class MockPdkShippingMethodRepository extends AbstractPdkShippingMethodRepository
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
