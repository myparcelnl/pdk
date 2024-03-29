<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Context\Model\ProductDataContext;

/**
 * @property ProductDataContext[] $items
 */
class ProductDataContextCollection extends Collection
{
    protected $cast = ProductDataContext::class;
}
