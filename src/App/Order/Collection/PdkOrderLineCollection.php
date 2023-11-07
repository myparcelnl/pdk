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

    /**
     * @return int
     */
    public function getTotalWeight(): int
    {
        return $this->reduce(static function (int $carry, PdkOrderLine $line) {
            return $carry + $line->getTotalWeight();
        }, 0);
    }

    /**
     * @return bool
     */
    public function isDeliverable(): bool
    {
        return $this->containsStrict('product.isDeliverable', true);
    }

    /**
     * @return self
     */
    public function onlyDeliverable(): self
    {
        return $this->where('product.isDeliverable', true);
    }
}
