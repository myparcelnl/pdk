<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassProperty;

/**
 * @property ClassProperty[] $items
 */
class ClassPropertyCollection extends Collection
{
    protected $cast = ClassProperty::class;
}
