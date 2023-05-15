<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Collection;

use MyParcelNL\Pdk\App\Cart\Model\PdkCartFee;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\App\Cart\Model\PdkCartFee[] $items
 */
class PdkCartFeeCollection extends Collection
{
    protected $casts = PdkCartFee::class;
}
