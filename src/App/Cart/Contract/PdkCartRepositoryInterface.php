<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Collection\PdkCartCollection;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;

interface PdkCartRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve a cart.
     */
    public function get($input): PdkCart;

    /**
     * Retrieve multiple carts.
     */
    public function getMany($input): PdkCartCollection;
}
