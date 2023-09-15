<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkOrderLine[] $items
 */
class PdkOrderLineCollection extends Collection
{
    protected $cast = PdkOrderLine::class;

    public function getTotalWeight(): int
    {
        return $this->reduce(static fn($carry, $line) => $carry + $line->quantity * $line->product->weight, 0);
    }

    public function isDeliverable(): bool
    {
        return $this->containsStrict('product.isDeliverable', true);
    }
}
