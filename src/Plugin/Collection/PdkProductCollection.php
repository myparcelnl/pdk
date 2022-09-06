<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkProduct[] $items
 */
class PdkProductCollection extends Collection
{
    protected $cast = PdkProduct::class;
}
