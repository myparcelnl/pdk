<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

use MyParcelNL\Pdk\Base\Support\Str;

interface Arrayable
{
    // Starts at 8 to support Str::CASE_* constants
    public const SKIP_NULL = 8;
    public const STORABLE  = 16;
    public const RECURSIVE = 32;
    // Combinations
    public const STORABLE_NULL = self::STORABLE | self::SKIP_NULL | self::RECURSIVE;
    public const ENCODED       = self::SKIP_NULL | Str::CASE_SNAKE | self::RECURSIVE;

    /**
     * Get the instance as an array.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array;
}
