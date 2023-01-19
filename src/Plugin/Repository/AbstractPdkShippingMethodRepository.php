<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;

abstract class AbstractPdkShippingMethodRepository extends Repository implements
    PdkShippingMethodRepositoryInterface
{
    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod
     */
    abstract public function get($input): PdkShippingMethod;

    /**
     * @param $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkShippingMethodCollection
     */
    public function getMany($input): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection(array_map([$this, 'get'], Utils::toArray($input)));
    }
}
