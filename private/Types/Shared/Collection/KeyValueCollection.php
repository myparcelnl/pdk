<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\KeyValue;

/**
 * @property KeyValue[] $items
 */
class KeyValueCollection extends Collection
{
    protected $cast = KeyValue::class;
}
