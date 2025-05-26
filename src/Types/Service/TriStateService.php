<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Types\Service;

use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

class TriStateService implements TriStateServiceInterface
{
    public const INHERIT  = -1;
    public const DISABLED = 0;
    public const ENABLED  = 1;
    /**
     * This type is cast to -1, 0 or 1.
     */
    public const TYPE_STRICT = 'triState';
    /**
     * This type is cast to -1 if the value is falsy, but is preserved otherwise.
     */
    public const TYPE_COERCED = 'triStateCoerced';
    /**
     * Replaces falsy values with '' and casts others to string.
     */
    public const TYPE_STRING = 'triStateString';

    /**
     * @param  mixed $value
     *
     * @return int
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
     * @param  mixed $value
     *
     * @return string|int
     */
    public function coerceString($value)
    {
        if (self::INHERIT === $value) {
            return self::INHERIT;
        }

        return empty($value) ? '' : (string) $value;
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
     * @param  mixed ...$values
     *
     * @return null|string
     */
    public function resolveString(...$values): ?string
    {
        $value = $this->resolveValues($values);

        return $value ? (string) $value : '';
    }

    /**
     * @param  array $values
     *
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

            if (self::INHERIT === $value || $value === '-1') {
                continue;
            }

            // Dan controleren of het een geldige waarde is
            if ($value === 'none' || empty($value)) {
                continue;
            }

            return $value;
        }

        return null;
    }
}
