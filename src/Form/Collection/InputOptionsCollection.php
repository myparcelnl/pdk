<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\InputOptions;

/**
 * @property \MyParcelNL\Pdk\Form\Model\InputOptions[] $items
 */
class InputOptionsCollection extends Collection
{
    protected $cast = InputOptions::class;
}
