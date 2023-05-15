<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Context\Model\OrderDataContext;

/**
 * @property \MyParcelNL\Pdk\Context\Model\OrderDataContext[] $items
 */
class OrderDataContextCollection extends Collection
{
    protected $cast = OrderDataContext::class;
}
