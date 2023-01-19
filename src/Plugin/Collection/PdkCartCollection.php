<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkCart[] $items
 */
class PdkCartCollection extends Collection
{
    protected $cast = PdkCart::class;
}
