<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Repository;

use MyParcelNL\Pdk\App\Cart\Collection\PdkCartCollection;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;

abstract class AbstractPdkCartRepository extends Repository implements PdkCartRepositoryInterface
{
    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Cart\Model\PdkCart
     */
    abstract public function get($input): PdkCart;

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Cart\Collection\PdkCartCollection
     */
    public function getMany($input): PdkCartCollection
    {
        return new PdkCartCollection(array_map([$this, 'get'], Utils::toArray($input)));
    }
}
