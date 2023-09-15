<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

interface ElementInterface extends Arrayable
{
    public function builder(callable $callback): ElementInterface;
}
