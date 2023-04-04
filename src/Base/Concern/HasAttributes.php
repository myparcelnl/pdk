<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
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
     * @var array
     */
    protected $guarded = [];

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
     * @param  null|int $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function attributesToArray(?int $flags = null): array
    {
        $attributes        = $this->getAttributes($flags);
        $mutatedAttributes = $this->getMutatedAttributes();

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            $mutatedAttributes,
            $flags
        );

        return $this->addCastAttributesToArray(
            $attributes,
            $mutatedAttributes,
            $flags
        );
    }

    /**
     * @param  string|array $attributes
     * @param  null|int     $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function except($attributes, ?int $flags = null): array
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $results = [];

        foreach ($this->attributes as $attribute => $value) {
            if (in_array($attribute, $attributes, true)) {
                continue;
            }

            $value = $this->getAttribute($attribute);

            if ($flags & Arrayable::SKIP_NULL && $value === null) {
                continue;
            }

            $results[$attribute] = $value;
        }

        return $results;
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getAttribute(string $key)
    {
        if (! $key) {
            return null;
        }

        $key = $this->convertAttributeCase($key);

        if ($this->isGuarded($key)) {
            return $this->guarded[$key];
        }

        if (array_key_exists($key, $this->getAttributes()) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return null;
    }

    /**
     * Get all the current attributes on the model.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function getAttributes(?int $flags = null): array
    {
        $attributes = $this->attributes;

        if ($flags) {
            if ($flags & Arrayable::SKIP_NULL) {
                $attributes = array_filter($attributes, static function ($value) {
                    return $value !== null;
                });
            }

            if ($flags & Arrayable::CASE_SNAKE || $flags & Arrayable::CASE_KEBAB || $flags & Arrayable::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($this->attributes, $this->getFlagCase($flags));
            }
        }

        return $attributes;
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
     * @param  null|int    $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function only($attributes, ?int $flags = null): array
    {
        $results = [];

        foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
            $value = $this->getAttribute($attribute);

            if ($flags & Arrayable::SKIP_NULL && $value === null) {
                continue;
            }

            $results[$attribute] = $value;
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

        if ($this->isGuarded($key)) {
            return $this;
        }

        // Invalidate cast cache
        if (array_key_exists($key, $this->classCastCache)) {
            unset($this->classCastCache[$key]);
        }

        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Add the cast attributes to the attributes array.
     *
     * @param  array    $attributes
     * @param  array    $mutatedAttributes
     * @param  null|int $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes, ?int $flags): array
    {
        $koekje = $this->getCasts();
        foreach ($this->getCasts() as $key => $value) {
            $originalKey = $this->convertAttributeCase($key);
            $key         = $this->convertAttributeCase($key, $flags);

            if (! array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes, true)) {
                continue;
            }

            // Here we will cast the attribute. Then, if the cast is a date or datetime cast
            // then we will serialize the date for the array. This will convert the dates
            // to strings based on the date format specified for these Eloquent models.
            $attributes[$key] = $this->castAttribute($originalKey, $attributes[$key]);

            // If the attribute cast was a date or a datetime, we will serialize the date as
            // a string. This allows the developers to customize how dates are serialized
            // into an array without affecting how they are persisted into the storage.
            if ($attributes[$key] && in_array($value, ['date', 'datetime'])) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] instanceof DateTimeInterface
                && $this->isClassCastable($originalKey)) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] instanceof Arrayable) {
                $attributes[$key] = $attributes[$key]->toArray($flags);
            }

            if ($flags & Arrayable::SKIP_NULL && null === $attributes[$key]) {
                unset($attributes[$key]);
            }

            if ($flags & Arrayable::CASE_SNAKE || $flags & Arrayable::CASE_KEBAB || $flags & Arrayable::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($this->attributes, $this->getFlagCase($flags));
            }
        }

        return $attributes;
    }

    /**
     * @param  array    $attributes
     * @param  array    $mutatedAttributes
     * @param  null|int $flags
     *
     * @return array
     */
    protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes, ?int $flags): array
    {
        foreach ($mutatedAttributes as $key) {
            $originalKey = $this->convertAttributeCase($key);
            $key         = $this->convertAttributeCase($key, $flags);

            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $attributes[$key] = $this->mutateAttributeForArray($originalKey, $attributes[$key]);

            if ($flags & Arrayable::SKIP_NULL && null === $attributes[$key]) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed $value
     *
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    protected function asDate($value): DateTimeImmutable
    {
        return $this->asDateTime($value)
            ->setTime(0, 0);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  \DateTimeInterface|string|array{date: string, timezone: string, timezone_type: int} $value
     *
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    protected function asDateTime($value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($value);
        }

        if (is_array($value) && isset($value['date'], $value['timezone'])) {
            return new DateTimeImmutable($value['date'], new DateTimeZone($value['timezone']));
        }

        foreach ($this->dateFormats as $dateFormat) {
            $date = DateTimeImmutable::createFromFormat($dateFormat, (string) $value);

            if ($date) {
                return $date;
            }
        }

        return new DateTimeImmutable();
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed $value
     *
     * @return int
     * @throws \Exception
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \Exception
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
            case DateTimeImmutable::class:
                $value = $this->asDateTime($value);
                break;
            case 'timestamp':
                $value = $this->asTimestamp($value);
                break;
            default:
                if ($this->isClassCastable($key)) {
                    $value = $this->getClassCastableAttributeValue($key, $value);
                }
                break;
        }

        return $value;
    }

    /**
     * @param  string   $key
     * @param  null|int $flags
     *
     * @return string
     */
    protected function convertAttributeCase(string $key, ?int $flags = null): string
    {
        $case = $this->getFlagCase($flags);

        return Str::{$case}($key);
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getClassCastableAttributeValue(string $key, $value)
    {
        if (isset($this->classCastCache[$key])) {
            return $this->classCastCache[$key];
        }

        $value = $this->getCastModel($key, $value);

        if (! is_object($value)) {
            unset($this->classCastCache[$key]);
        } else {
            $this->classCastCache[$key] = $value;
        }

        return $value;
    }

    /**
     * @param  null|int $flags
     *
     * @return null|string
     */
    protected function getFlagCase(?int $flags): ?string
    {
        if ($flags & Arrayable::CASE_SNAKE) {
            return 'snake';
        }

        if ($flags & Arrayable::CASE_KEBAB) {
            return 'kebab';
        }

        if ($flags & Arrayable::CASE_STUDLY) {
            return 'studly';
        }

        return 'camel';
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
     * @param  string $key
     *
     * @return bool
     */
    protected function isGuarded(string $key): bool
    {
        if (array_key_exists($key, $this->guarded)) {
            return true;
        }

        return false;
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
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

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getCastModel(string $key, $value)
    {
        $class     = $this->getCasts()[$key];
        $arguments = null;

        if ($class !== $value) {
            $arguments = $value instanceof Arrayable ? $value->toArray() : $value;
        }

        if (is_a($arguments, $class)) {
            return $arguments;
        }

        try {
            return new $class($arguments);
        } catch (Throwable $e) {
            throw new InvalidCastException($key, $class, $arguments, $e);
        }
    }
}
