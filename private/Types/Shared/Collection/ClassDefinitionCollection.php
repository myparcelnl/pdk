<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;

/**
 * @property ClassDefinition[] $items
 */
class ClassDefinitionCollection extends Collection
{
    protected $cast = ClassDefinition::class;
}
