<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Types\Contract;

interface TriStateServiceInterface
{
    /**
     * Casts a value to -1, 0 or 1.
     *
     * @param  mixed $value
     *
     * @return int
     */
    public function cast($value): int;

    /**
     * Coerces empty values to -1, but preserves other values.
     * @template T of mixed
     *
     * @param  T ...$values
     *
     * @return int|T
     */
    public function coerce(...$values);

    /**
     * Turns empty values into -1, but preserves other values and casts them to string.
     *
     * @param  mixed $value
     *
     * @return int|string
     */
    public function coerceString($value);

    /**
     * Resolves to the first value that is not -1.
     *
     * @param  mixed ...$values
     *
     * @return mixed
     */
    public function resolve(...$values);

    /**
     * Resolves to the first value that is not -1 and casts it to string.
     *
     * @param  mixed ...$values
     *
     * @return int|string
     */
    public function resolveString(...$values);
}
