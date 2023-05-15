<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Helper\Shared\Model\ClassMethod;

/**
 * @property ClassMethod[] $items
 */
class ClassMethodCollection extends Collection
{
    protected $cast = ClassMethod::class;
}
