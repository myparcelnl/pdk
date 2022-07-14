<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use DateTime;
use DateTimeInterface;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Model\InvalidCastException;
use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Base\Utils;
use MyParcelNL\Sdk\src\Support\Str;

trait HasAttributes
{
    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * @var string[]
     */
    protected static $primitiveCastTypes = [
        'array',
        'bool',
        'date',
        'datetime',
        'float',
        'int',
        'string',
        'timestamp',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var string
     */
    protected $dateFormats = ['Y-m-d H:i:s', 'Y-m-d', DateTime::ATOM];

    /**
     * The attributes that have been cast using custom classes.
     *
     * @var array
     */
    private $classCastCache = [];

    /**
     * Extract and cache all the mutated attributes of a class.
     *
     * @param  string $class
     *
     * @return void
     */
    public static function cacheMutatedAttributes(string $class): void
    {
        static::$mutatorCache[$class] = (new Collection(static::getMutatorMethods($class)))
            ->map(function ($match) {
                return Str::camel($match);
            })
            ->all();
    }

    /**
     * Get all the attribute mutator methods.
     *
     * @param  mixed $class
     *
     * @return array
     */
    protected static function getMutatorMethods($class): array
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[1];
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @param  null|string $case - camel, snake, studly etc.
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    public function attributesToArray(string $case = null): array
    {
        $attributes = $this->getAttributes($case);

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            array_keys($attributes),
            $case
        );

        foreach ($this->getArrayableAttributes() as $key => $value) {
            $key              = $this->convertAttributeCase($key, $case);
            $attributes[$key] = $this->mutateAttributeForArray($key, $value);
        }

        $mutatedAttributes = $this->getMutatedAttributes();

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            $mutatedAttributes,
            $case
        );

        return $this->addCastAttributesToArray(
            $attributes,
            $mutatedAttributes
        );
    }

    /**
     * Decode the given float.
     *
     * @param  mixed $value
     *
     * @return float
     */
    public function fromFloat($value): float
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float) $value;
        }
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string $value
     * @param  bool   $asObject
     *
     * @return mixed
     */
    public function fromJson(string $value, bool $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    public function getAttribute(string $key)
    {
        if (! $key) {
            return null;
        }

        $key = $this->convertAttributeCase($key);

        if (array_key_exists($key, $this->getAttributes()) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return null;
    }

    /**
     * Get all the current attributes on the model.
     *
     * @param  null|string $case
     *
     * @return array
     */
    public function getAttributes(string $case = null): array
    {
        $this->mergeAttributesFromClassCasts();

        return Utils::changeArrayKeysCase($this->attributes, $case);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, $this->createMutatorName('get', $key));
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function hasSetMutator(string $key): bool
    {
        return method_exists($this, $this->createMutatorName('set', $key));
    }

    /**
     * Get a subset of the model's attributes.
     *
     * @param  array|mixed $attributes
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    public function only($attributes): array
    {
        $results = [];

        foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
            $results[$attribute] = $this->getAttribute($attribute);
        }

        return $results;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        $key = $this->convertAttributeCase($key);

        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Add the cast attributes to the attributes array.
     *
     * @param  array $attributes
     * @param  array $mutatedAttributes
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes): array
    {
        foreach ($this->getCasts() as $key => $value) {
            $key = $this->convertAttributeCase($key);

            if (! array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes, true)) {
                continue;
            }

            // Here we will cast the attribute. Then, if the cast is a date or datetime cast
            // then we will serialize the date for the array. This will convert the dates
            // to strings based on the date format specified for these Eloquent models.
            $attributes[$key] = $this->castAttribute($key, $attributes[$key]);

            // If the attribute cast was a date or a datetime, we will serialize the date as
            // a string. This allows the developers to customize how dates are serialized
            // into an array without affecting how they are persisted into the storage.
            if ($attributes[$key] && in_array($value, ['date', 'datetime'])) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] instanceof DateTimeInterface
                && $this->isClassCastable($key)) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] instanceof Arrayable) {
                $attributes[$key] = $attributes[$key]->toArray();
            }
        }

        return $attributes;
    }

    /**
     * @param  array       $attributes
     * @param  array       $mutatedAttributes
     * @param  null|string $case
     *
     * @return array
     */
    protected function addMutatedAttributesToArray(
        array   $attributes,
        array   $mutatedAttributes,
        ?string $case = null
    ): array {
        foreach ($mutatedAttributes as $key) {
            $key = $this->convertAttributeCase($key, $case);

            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $attributes[$key] = $this->mutateAttributeForArray($key, $attributes[$key]);
        }

        return $attributes;
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed $value
     *
     * @return \DateTime
     */
    protected function asDate($value): DateTime
    {
        return $this->asDateTime($value)
            ->setTime(0, 0);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     *
     * @return \DateTime
     */
    protected function asDateTime($value): DateTime
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format($this->dateFormats[0]);
        }

        foreach ($this->dateFormats as $dateFormat) {
            $date = DateTime::createFromFormat($dateFormat, $value);

            if ($date) {
                return $date;
            }
        }

        return new DateTime();
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed $value
     *
     * @return int
     */
    protected function asTimestamp($value): int
    {
        return $this->asDateTime($value)
            ->getTimestamp();
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    protected function castAttribute(string $key, $value)
    {
        $castType = $this->getCastType($key);

        if (null === $value) {
            return null;
        }

        switch ($castType) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = $this->fromFloat($value);
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'bool':
                $value = (bool) $value;
                break;
            case 'date':
                $value = $this->asDate($value);
                break;
            case 'datetime':
            case DateTime::class:
                $value = $this->asDateTime($value);
                break;
            case 'timestamp':
                $value = $this->asTimestamp($value);
                break;
        }

        if ($this->isClassCastable($key)) {
            $value = $this->getClassCastableAttributeValue($key, $value);
        }

        return $value;
    }

    /**
     * @param  string      $key
     * @param  null|string $case
     *
     * @return string
     */
    protected function convertAttributeCase(string $key, ?string $case = null): string
    {
        return Str::{$case ?? 'camel'}($key);
    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @return array
     */
    protected function getArrayableAttributes(): array
    {
        return $this->getArrayableItems($this->getAttributes());
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param  array $values
     *
     * @return array
     */
    protected function getArrayableItems(array $values): array
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }

        return $values;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string $key
     *
     * @return mixed
     */
    protected function getAttributeFromArray(string $key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    protected function getAttributeValue(string $key)
    {
        return $this->transformModelValue($key, $this->getAttributeFromArray($key));
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function getCastType(string $key): string
    {
        return $this->getCasts()[$this->convertAttributeCase($key)];
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    protected function getCasts(): array
    {
        return Utils::changeArrayKeysCase($this->casts);
    }

    /**
     * Cast the given attribute using a custom cast class.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    protected function getClassCastableAttributeValue(string $key, $value)
    {
        if (isset($this->classCastCache[$key])) {
            return $this->classCastCache[$key];
        }

        $class     = $this->getCasts()[$key];
        $arguments = $value instanceof Arrayable ? $value->toArray() : $value;

        if (! is_array($arguments)) {
            throw new InvalidCastException($key, $class, $arguments);
        }

        $value = new $class($arguments);

        if (! is_object($value)) {
            unset($this->classCastCache[$key]);
        } else {
            $this->classCastCache[$key] = $value;
        }

        return $value;
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    protected function getMutatedAttributes(): array
    {
        $class = static::class;

        if (! isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * Determine whether an attribute should be cast to a native type.
     *
     * @param  string            $key
     * @param  array|string|null $types
     *
     * @return bool
     */
    protected function hasCast(string $key, $types = null): bool
    {
        if (array_key_exists($key, $this->getCasts())) {
            return ! $types || in_array($this->getCastType($key), (array) $types, true);
        }

        return false;
    }

    /**
     * Determine if the given key is cast using a custom class.
     *
     * @param  string $key
     *
     * @return bool
     */
    protected function isClassCastable(string $key): bool
    {
        $castType = $this->parseCasterClass($this->getCasts()[$key]);

        if (in_array($castType, self::$primitiveCastTypes)) {
            return false;
        }

        if (class_exists($castType)) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    protected function mergeAttributesFromClassCasts(): void
    {
        $attributes = [];

        foreach ($this->classCastCache as $key => $value) {
            $key          = $this->convertAttributeCase($key);
            $attributes[] = [$key => $value];
        }

        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function mutateAttribute(string $key, $value)
    {
        return $this->{$this->createMutatorName('get', $key)}($value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function mutateAttributeForArray(string $key, $value)
    {
        $value = $this->mutateAttribute($key, $value);

        return $value instanceof Arrayable ? $value->toArray() : $value;
    }

    /**
     * Parse the given caster class, removing any arguments.
     *
     * @param  string $class
     *
     * @return string
     */
    protected function parseCasterClass(string $class): string
    {
        return ! Str::contains($class, ':')
            ? $class
            : explode(':', $class, 2)[0];
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface $date
     *
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormats[0]);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function setMutatedAttributeValue(string $key, $value)
    {
        return $this->{$this->createMutatorName('set', $key)}($value);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    protected function transformModelValue(string $key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    /**
     * @param  string $type
     * @param  string $key
     *
     * @return void
     */
    private function createMutatorName(string $type, string $key): string
    {
        return sprintf('%s%sAttribute', $type, Str::studly($key));
    }
}
