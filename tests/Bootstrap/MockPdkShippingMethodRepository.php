<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use Symfony\Contracts\Service\ResetInterface;

class MockPdkShippingMethodRepository implements PdkShippingMethodRepositoryInterface, ResetInterface
{
    /**
     * @var array<\MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod>
     */
    private $shippingMethods = [];

    /**
     * @param  \MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod ...$shippingMethods
     *
     * @return void
     */
    public function add(PdkShippingMethod ...$shippingMethods): void
    {
        foreach ($shippingMethods as $method) {
            $this->shippingMethods[] = $method;
        }
    }

    /**
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection
     */
    public function all(): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection($this->shippingMethods);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->shippingMethods = [];
    }
}
