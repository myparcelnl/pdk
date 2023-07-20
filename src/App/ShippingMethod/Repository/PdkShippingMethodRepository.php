<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Repository;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Repository\StorageRepository;

class PdkShippingMethodRepository extends StorageRepository implements PdkShippingMethodRepositoryInterface
{
    public function all(): PdkShippingMethodCollection
    {
        // TODO: Implement all() method.
    }

    public function get($input): PdkShippingMethod
    {
        return $this->retrieve($input);
    }

    public function getMany($input): PdkShippingMethodCollection
    {
        return new PdkShippingMethodCollection(array_map([$this, 'get'], Utils::toArray($input)));
    }
}
