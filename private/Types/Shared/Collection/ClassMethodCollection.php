<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassMethod;

/**
 * @property ClassMethod[] $items
 */
class ClassMethodCollection extends Collection
{
    protected $cast = ClassMethod::class;
}
