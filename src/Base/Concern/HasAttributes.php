<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Support\Arrayable;
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $types = [];

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
     * Append attributes to query when building a query.
     *
     * @param  array|string $attributes
     *
     * @return self
     */
    public function append($attributes): self
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @param  null|string $case - camel, snake, studly etc.
     *
     * @return array
     */
    public function attributesToArray(string $case = null): array
    {
        $attributes = $case ? $this->getChangedCaseAttributes($case) : $this->attributes;

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            array_keys($attributes),
            $case
        );

        foreach ($this->getArrayableAttributes() as $key => $value) {
            $key              = $this->convertAttributeCase($key, $case);
            $attributes[$key] = $this->mutateAttributeForArray($key, $value);
        }

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            $this->getMutatedAttributes(),
            $case
        );

        foreach ($this->getArrayableAppends() as $key) {
            $key              = $this->convertAttributeCase($key, $case);
            $attributes[$key] = $this->mutateAttributeForArray($key, null);
        }

        return $attributes;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if (! $key) {
            return null;
        }

        $key = $this->convertAttributeCase($key);

        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return null;
    }

    /**
     * Get all the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  null|string $case - camel, snake, studly, etc.
     *
     * @return array
     */
    public function getChangedCaseAttributes(string $case = null): array
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            $attributes[Str::{$case ?? 'camel'}($key)] = $value;
        }

        return $attributes;
    }

    /**
     * Return whether the accessor attribute has been appended.
     *
     * @param  string $attribute
     *
     * @return bool
     */
    public function hasAppended(string $attribute): bool
    {
        return in_array($attribute, $this->appends, true);
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
     * Set the accessors to append to model arrays.
     *
     * @param  array $appends
     *
     * @return self
     */
    public function setAppends(array $appends): self
    {
        $this->appends = $appends;

        return $this;
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
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $key              = $this->convertAttributeCase($key, $case);
            $attributes[$key] = $this->mutateAttributeForArray($key, $attributes[$key]);
        }

        return $attributes;
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
     * Get all the appendable values that are arrayable.
     *
     * @return array
     */
    protected function getArrayableAppends(): array
    {
        if (! count($this->appends)) {
            return [];
        }

        return $this->getArrayableItems(
            array_combine($this->appends, $this->appends)
        );
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
     */
    protected function getAttributeValue(string $key)
    {
        return $this->transformModelValue($key, $this->getAttributeFromArray($key));
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
     */
    protected function transformModelValue(string $key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
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
