<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use ArrayAccess;
use MyParcelNL\Pdk\Base\Concern\HasAttributes;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Model implements Arrayable, ArrayAccess
{
    use HasAttributes;

    /**
     * @var array
     */
    protected static $booted;

    /**
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->guarded    = Utils::changeArrayKeysCase($this->guarded);
        $this->attributes = $this->guarded + Utils::changeArrayKeysCase($this->attributes);

        $this->bootIfNotBooted();
        $this->initializeTraits();

        $data = Arr::only(Utils::changeArrayKeysCase($data ?? []), array_keys($this->attributes));

        $this->fill($data + $this->attributes);
    }

    /**
     * Boot all bootable traits on the model.
     *
     * @return void
     */
    protected static function bootTraits(): void
    {
        $class = static::class;

        static::$traitInitializers[$class] = [];

        foreach (Utils::getClassParentsRecursive($class) as $trait) {
            $classBasename = Utils::classBasename($trait);
            $method        = "initialize$classBasename";

            if (method_exists($class, $method)) {
                static::$traitInitializers[$class][] = $method;

                static::$traitInitializers[$class] = array_unique(
                    static::$traitInitializers[$class]
                );
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
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
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
     * @param  string $key
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
        foreach ($attributes as $key => $value) {
            if (is_string($value) && class_exists($value) && Str::contains($value, '\\')) {
                $value = new $value();
            }

            $key = $this->convertAttributeCase($key);

            if (! array_key_exists($key, $this->attributes)) {
                continue;
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function offsetExists($offset): bool
    {
        return null !== $this->getAttribute($offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed $offset
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
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
        unset($this->attributes[$this->convertAttributeCase($offset)]);
    }

    /**
     * Convert the model instance to an array.
     *
     * @param  null|int $flags
     *
     * @return array
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
        return $this->attributesToArray(Arrayable::SKIP_NULL);
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toKebabCaseArray(): array
    {
        return $this->attributesToArray(Arrayable::CASE_KEBAB);
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toSnakeCaseArray(): array
    {
        return $this->attributesToArray(Arrayable::CASE_SNAKE);
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStudlyCaseArray(): array
    {
        return $this->attributesToArray(Arrayable::CASE_STUDLY);
    }

    /**
     * Check if the model needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted(): void
    {
        if (isset(static::$booted[static::class])) {
            return;
        }

        static::$booted[static::class] = true;
        static::bootTraits();
    }

    /**
     * Initialize any initializable traits on the model.
     *
     * @return void
     */
    protected function initializeTraits(): void
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }
}
