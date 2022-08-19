<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderLine;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\OrderLine[] $items
 */
class OrderLineCollection extends Collection
{
    protected $cast = OrderLine::class;
}
