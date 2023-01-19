<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkCartFee;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkCartFee[] $items
 */
class PdkCartFeeCollection extends Collection
{
    protected $casts = PdkCartFee::class;
}
