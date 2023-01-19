<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

interface StorableArrayable
{
    /**
     * @return array
     */
    public function toStorableArray(): array;
}
