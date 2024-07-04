<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use function MyParcelNL\Pdk\Tests\factory;

class MockPdkShippingMethodRepository implements PdkShippingMethodRepositoryInterface
{
    public function all(): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection([
            factory(PdkShippingMethod::class)
                ->withId('shipping:1')
                ->withName('Shipping 1')
                ->withIsEnabled(true)
                ->make(),
            factory(PdkShippingMethod::class)
                ->withId('shipping:2')
                ->withName('Shipping 2')
                ->withIsEnabled(false)
                ->make(),
            factory(PdkShippingMethod::class)
                ->withId('shipping:3')
                ->withName('Shipping 3')
                ->withIsEnabled(true)
                ->make(),
        ]);
    }
}
