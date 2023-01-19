<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Collection\PdkCartCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;

abstract class AbstractPdkCartRepository extends Repository implements PdkCartRepositoryInterface
{
    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkCart
     */
    abstract public function get($input): PdkCart;

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkCartCollection
     */
    public function getMany($input): PdkCartCollection
    {
        return new PdkCartCollection(array_map([$this, 'get'], Utils::toArray($input)));
    }
}
