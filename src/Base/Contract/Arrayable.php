<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface Arrayable
{
    public const CASE_SNAKE  = 1;
    public const CASE_KEBAB  = 2;
    public const CASE_STUDLY = 4;
    public const SKIP_NULL   = 8;

    /**
     * Get the instance as an array.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array;
}
