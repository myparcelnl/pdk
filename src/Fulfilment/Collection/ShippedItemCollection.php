<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\ShippedItem;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\ShippedItem[] $items
 */
class ShippedItemCollection extends Collection
{
    protected $cast = ShippedItem::class;
}
