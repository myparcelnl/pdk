<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;
}
