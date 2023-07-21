<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface StorableArrayable extends Arrayable
{
    /**
     * @return array
     */
    public function toStorableArray(): array;
}
