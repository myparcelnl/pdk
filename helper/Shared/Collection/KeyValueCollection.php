<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Helper\Shared\Model\KeyValue;

/**
 * @property KeyValue[] $items
 */
class KeyValueCollection extends Collection
{
    protected $cast = KeyValue::class;
}
