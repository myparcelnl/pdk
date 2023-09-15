<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Types\Service;

use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

class TriStateService implements TriStateServiceInterface
{
    final public const INHERIT  = -1;
    final public const DISABLED = 0;
    final public const ENABLED  = 1;
    /**
     * This type is cast to -1, 0 or 1.
     */
    final public const TYPE_STRICT = 'triState';
    /**
     * This type is cast to -1 if the value is falsy, but is preserved otherwise.
     */
    final public const TYPE_COERCED = 'triStateCoerced';
    /**
     * Replaces falsy values with '' and casts others to string.
     */
    final public const TYPE_STRING = 'triStateString';

    /**
     * @param  mixed $value
     */
    public function cast($value): int
    {
        $int = (int) $value;

        if (self::INHERIT === $int) {
            return self::INHERIT;
        }

        return $int ? self::ENABLED : self::DISABLED;
    }

    /**
     * @param  mixed ...$values
     *
     * @return int|mixed
     */
    public function coerce(...$values)
    {
        return $this->resolveValues($values) ?? self::INHERIT;
    }

    /**
     * Resolves to the first non-INHERIT value.
     *
     * @param  mixed ...$values
     *
     * @return mixed
     */
    public function resolve(...$values)
    {
        return $this->resolveValues($values) ?? self::DISABLED;
    }

    /**
     * @return null|int|mixed
     */
    private function resolveValues(array $values)
    {
        foreach ($values as $value) {
            if (is_bool($value)) {
                return $this->cast($value);
            }

            if (self::DISABLED === $value) {
                return self::DISABLED;
            }

            if (! empty($value) && self::INHERIT !== $value) {
                return $value;
            }
        }

        return null;
    }
}
