<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Collection;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\App\Cart\Model\PdkCart[] $items
 */
class PdkCartCollection extends Collection
{
    protected $cast = PdkCart::class;
}
