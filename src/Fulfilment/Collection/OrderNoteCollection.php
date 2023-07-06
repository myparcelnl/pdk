<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\Order[] $items
 */
class OrderNoteCollection extends Collection
{
    /**
     * @var class-string
     */
    protected $cast = OrderNote::class;
}
