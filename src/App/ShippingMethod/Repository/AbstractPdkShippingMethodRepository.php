<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Repository;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;

abstract class AbstractPdkShippingMethodRepository extends Repository implements
    PdkShippingMethodRepositoryInterface
{
    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod
     */
    abstract public function get($input): PdkShippingMethod;

    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection
     */
    public function getMany($input): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection(array_map([$this, 'get'], Utils::toArray($input)));
    }
}
