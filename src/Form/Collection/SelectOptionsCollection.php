<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\SelectOptions;

/**
 * @property \MyParcelNL\Pdk\Form\Model\SelectOptions[] $items
 */
class SelectOptionsCollection extends Collection
{
    protected $cast = SelectOptions::class;
}
