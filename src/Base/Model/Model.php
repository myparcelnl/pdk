<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use ArrayAccess;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Concern\HasAttributes;
use MyParcelNL\Pdk\Base\Concern\HidesAttributes;
use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use ReturnTypeWillChange;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Model implements Arrayable, ArrayAccess
{
    use HasAttributes;
    use HidesAttributes;

    /**
     * @var array
     */
    protected static $booted;

    /**
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes = Utils::changeArrayKeysCase($this->attributes);
        $this->guarded    = Utils::changeArrayKeysCase($this->guarded);
        $data             = Utils::changeArrayKeysCase($data ?? []);

        $this->bootIfNotBooted();
        $this->initializeTraits();
        $this->validateAttributes($data);
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
            if ($this->isGuarded($key)) {
                unset($this->attributes[$key]);
                continue;
            }

            if (is_string($value) && class_exists($value) && Str::contains($value, '\\')) {
                $value = new $value();
            }

            $key = $this->convertAttributeCase($key);

            if ($this->attributes[$key] && null === $value) {
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
    #[ReturnTypeWillChange]
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
    #[ReturnTypeWillChange]
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
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toArray(): array
    {
        return $this->attributesToArray();
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toSnakeCaseArray(): array
    {
        return $this->attributesToArray('snake');
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

    /**
     * @param  null|array $data
     *
     * @return void
     */
    private function validateAttributes(?array $data): void
    {
        if (! $data) {
            return;
        }

        $unknownAttributes = array_diff_key($data, $this->attributes);

        if (empty($unknownAttributes)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unknown attribute(s) passed: "%s". Attributes must be defined in $model->attributes.',
                implode('", "', array_keys($unknownAttributes))
            )
        );
    }
}
