<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Contract;

interface SchemaInterface
{
    /**
     * @return array
     */
    public function getSchema(): array;
}
