<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrderLine;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkOrderLine[] $items
 */
class PdkOrderLineCollection extends Collection
{
    protected $cast = PdkOrderLine::class;
}
