<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\Context\OrderDataContext;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\Context\OrderDataContext[] $items
 */
class OrderDataContextCollection extends Collection
{
    protected $cast = OrderDataContext::class;
}
