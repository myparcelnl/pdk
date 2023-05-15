<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Contract;

use MyParcelNL\Pdk\App\Cart\Collection\PdkCartCollection;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;

interface PdkCartRepositoryInterface
{
    public function get($input): PdkCart;

    public function getMany($input): PdkCartCollection;
}
