<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;

/**
 * @property \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface $items
 */
final class FactoryCollection extends Collection
{
    public function store(): self
    {
        return $this->map(function (FactoryInterface $item) {
            return $item->store();
        });
    }
}
