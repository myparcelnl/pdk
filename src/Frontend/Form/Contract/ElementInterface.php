<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

interface ElementInterface extends Arrayable
{
    /**
     * @param  callable $callback
     *
     * @return $this
     */
    public function builder(callable $callback): ElementInterface;
}
