<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface StorableArrayable extends Arrayable
{
    public function toStorableArray(): array;
}
