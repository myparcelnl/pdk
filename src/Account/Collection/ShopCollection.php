<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Collection;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Account\Model\Shop[] $items
 */
class ShopCollection extends Collection
{
    protected $cast = Shop::class;
}
