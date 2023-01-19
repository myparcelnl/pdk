<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Plugin\Collection\PdkCartCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;

interface PdkCartRepositoryInterface
{
    public function get($input): PdkCart;

    public function getMany($input): PdkCartCollection;
}
