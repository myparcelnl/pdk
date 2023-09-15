<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use ArrayAccess;
use MyParcelNL\Pdk\Base\Concern\HasAttributes;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\ModelInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Model implements Arrayable, ArrayAccess, ModelInterface
{
    use HasAttributes;

    /**
     * @var bool
     */
    protected $cloned = false;

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->guarded    = Utils::changeArrayKeysCase($this->guarded);
        $this->attributes = $this->guarded + Utils::changeArrayKeysCase($this->attributes);

        $convertedData = Utils::changeArrayKeysCase($data ?? []);

        $this->fill($convertedData + $this->attributes);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __call(string $method, array $parameters)
    {
        $trimmed   = str_replace(['get', 'set', 'Attribute'], '', $method);
        $attribute = Str::camel($trimmed);

        if (Str::contains($method, 'get')) {
            return $this->getAttribute($attribute);
        }

        if (Str::contains($method, 'set')) {
            $this->setAttribute($attribute, ...$parameters);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->cloned = true;

        $this->attributes = array_map(Utils::clone(...), $this->getAttributes());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @return bool
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __isset(string $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @return void
     */
    public function __set(string $key, mixed $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @return static
     */
    public function fill(array $attributes): self
    {
        foreach ($this->normalizeAttributes($attributes) as $key => $value) {
            if (! array_key_exists($key, $this->attributes)) {
                continue;
            }

            if (
                is_string($value)
                && class_exists($value)
                && $this->isClassCastable($key)
                && Str::contains($value, '\\')
            ) {
                $value = new $value();
            }

            if (null !== $this->attributes[$key] && null === $value) {
                continue;
            }

            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Determine if the given attribute exists.
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function offsetExists(mixed $offset): bool
    {
        return null !== $this->getAttribute($offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function offsetGet(mixed $offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$this->convertAttributeCase($offset)]);
    }

    /**
     * Convert the model instance to an array.
     *
     * @param  null|int $flags
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toArray(?int $flags = null): array
    {
        return $this->attributesToArray($flags);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toArrayWithoutNull(): array
    {
        return $this->toArray(Arrayable::SKIP_NULL);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toKebabCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_KEBAB);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toSnakeCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_SNAKE);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStudlyCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_STUDLY);
    }

    private function normalizeAttributes(array $attributes): array
    {
        $normalizedAttributes = [];

        foreach ($attributes as $initialKey => $value) {
            $caseKey = $this->convertAttributeCase($initialKey);
            $key     = $this->convertDeprecatedKey($caseKey);

            if (array_key_exists($key, $normalizedAttributes)) {
                continue;
            }

            $normalizedAttributes[$key] = $value;
        }

        return $normalizedAttributes;
    }
}
