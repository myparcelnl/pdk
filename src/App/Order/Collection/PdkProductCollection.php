<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkProduct[] $items
 */
class PdkProductCollection extends Collection
{
    protected $cast = PdkProduct::class;
}
