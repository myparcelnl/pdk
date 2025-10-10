<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use ArrayAccess;
use MyParcelNL\Pdk\Base\Concern\HasAttributes;
use MyParcelNL\Pdk\Base\Concern\OffsetGetByPhpVersion;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\ModelInterface;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Model implements StorableArrayable, ArrayAccess, ModelInterface
{
    use OffsetGetByPhpVersion;
    use HasAttributes;

    /**
     * @var array
     */
    protected static $booted = [];

    /**
     * @var array
     */
    protected static $traitInitializers;

    /**
     * @var bool
     */
    protected $cloned = false;

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->bootIfNotBooted();

        $this->guarded    = Utils::changeArrayKeysCase($this->guarded);
        $this->attributes = $this->guarded + Utils::changeArrayKeysCase($this->attributes);

        $this->initializeTraits();

        $convertedData = Utils::changeArrayKeysCase($data ?? []);

        $this->fill($convertedData + $this->attributes);
    }

    public static function isBooted(): bool
    {
        return isset(static::$booted[static::class]);
    }

    /**
     * @return void
     */
    protected static function bootTraits(): void
    {
        $class = static::class;

        static::$traitInitializers[$class] = [];

        $traits = Utils::getClassTraitsRecursive($class);

        foreach ($traits as $trait) {
            $baseName = Utils::classBasename($trait);
            $method   = sprintf('initialize%s', $baseName);

            if (method_exists($class, $method)) {
                static::$traitInitializers[$class][] = $method;
            }
        }
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        $attribute   = str_replace(['get', 'set', 'Attribute'], '', $method);

        if (Str::contains($method, 'get')) {
            $attribute = Str::camel($attribute);
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

        $this->attributes = array_map([Utils::class, 'clone'], $this->getAttributes());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function __isset(string $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param  array $attributes
     *
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
     * @param  mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return null !== $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed $offset
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[(Utils::changeCase($offset))]);
    }

    /**
     * Convert the model instance to an array.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        return $this->attributesToArray($flags);
    }

    /**
     * @return array
     */
    public function toArrayWithoutNull(): array
    {
        return $this->toArray(Arrayable::SKIP_NULL);
    }

    /**
     * @return array
     */
    public function toKebabCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_KEBAB);
    }

    /**
     * @return array
     */
    public function toSnakeCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_SNAKE);
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return $this->toArray(Arrayable::STORABLE_NULL);
    }

    /**
     * @return array
     */
    public function toStudlyCaseArray(): array
    {
        return $this->toArray(Arrayable::CASE_STUDLY);
    }

    /**
     * @return void
     */
    protected function bootIfNotBooted(): void
    {
        if (self::isBooted()) {
            return;
        }
        static::bootTraits();

        static::$booted[static::class] = true;
    }

    /**
     * @return void
     */
    protected function initializeTraits(): void
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }

    /**
     * @param  array $attributes
     *
     * @return array
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalizedAttributes = [];

        foreach ($attributes as $initialKey => $value) {
            $caseKey = Utils::changeCase($initialKey);
            $key     = $this->convertDeprecatedKey($caseKey);

            if (array_key_exists($key, $normalizedAttributes)) {
                continue;
            }

            $normalizedAttributes[$key] = $value;
        }

        return $normalizedAttributes;
    }
}
