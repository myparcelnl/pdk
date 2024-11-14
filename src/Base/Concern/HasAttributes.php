<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Str;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
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
        'boolean',
        'date',
        'datetime',
        'float',
        'int',
        'string',
        'timestamp',
        TriStateService::TYPE_STRICT,
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
     * List of deprecated attributes and their replacements.
     *
     * @var array<string, string>
     */
    protected $deprecated = [];

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
     */
    public function attributesToArray(?int $flags = null): array
    {
        $attributes = $this->getAttributes($flags);

        return $this->createArrayFromAttributes($attributes, $flags);
    }

    /**
     * @param  string|array $attributes
     * @param  null|int     $flags
     *
     * @return array
     */
    public function except($attributes, ?int $flags = null): array
    {
        return $this->createArrayFromAttributes(Arr::except($this->attributes, Arr::wrap($attributes)), $flags);
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
     */
    public function getAttribute(string $key)
    {
        if (! $key) {
            return null;
        }

        $key = Str::changeCase($key);

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
                $attributes = Arr::where($attributes, function ($value, $key) {
                    return null !== $value || $this->hasGetMutator($key);
                });
            }

            if ($flags & Str::CASE_SNAKE || $flags & Str::CASE_KEBAB || $flags & Str::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($this->attributes, $flags);
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
     * @param  string|array $attributes
     * @param  null|int     $flags
     *
     * @return array
     */
    public function only($attributes, ?int $flags = null): array
    {
        return $this->createArrayFromAttributes(Arr::only($this->attributes, Arr::wrap($attributes)), $flags);
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
        $key = $this->convertDeprecatedKey(Str::changeCase($key));

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
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes, ?int $flags): array
    {
        foreach ($this->getCasts() as $key => $value) {
            $originalKey = Str::changeCase($key);
            $key         = Str::changeCase($key, $flags);

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

            if ($attributes[$key] instanceof DateTimeInterface && $this->isClassCastable($originalKey)) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($flags & Arrayable::STORABLE && $attributes[$key] instanceof StorableArrayable) {
                $attributes[$key] = $attributes[$key]->toStorableArray();
            } elseif ($attributes[$key] instanceof Arrayable) {
                $attributes[$key] = $attributes[$key]->toArray($flags);
            }

            if ($flags & Arrayable::SKIP_NULL) {
                if (is_array($attributes[$key])) {
                    $attributes[$key] = Utils::filterNull($attributes[$key]);
                }

                if (null === $attributes[$key]) {
                    unset($attributes[$key]);
                }
            }

            if ($flags & Str::CASE_SNAKE || $flags & Str::CASE_KEBAB || $flags & Str::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($attributes, $flags);
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
            $originalKey = Str::changeCase($key);
            $key         = Str::changeCase($key, $flags);

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
     */
    protected function asDate($value): DateTimeImmutable
    {
        return $this
            ->asDateTime($value)
            ->setTime(0, 0);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  \DateTimeInterface|string|array{date: string, timezone: string, timezone_type: int} $value
     *
     * @return \DateTimeImmutable
     */
    protected function asDateTime($value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($value);
        }

        if (is_array($value) && isset($value['date'])) {
            try {
                return new DateTimeImmutable($value['date'], new DateTimeZone($value['timezone']));
            } catch (Exception $e) {
                Logger::error(
                    sprintf('Failed to create %s from array', DateTimeImmutable::class),
                    [
                        'value'     => $value,
                        'exception' => $e,
                    ]
                );

                return new DateTimeImmutable();
            }
        }

        foreach ($this->getDateFormats() as $dateFormat) {
            if (Pdk::get('defaultDateFormatShort') === $dateFormat) {
                $dateFormat = Pdk::get('defaultDateFormat');
                $value      .= ' 00:00:00';
            }

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
     */
    protected function asTimestamp($value): int
    {
        return $this
            ->asDateTime($value)
            ->getTimestamp();
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute(string $key, $value)
    {
        $castType = $this->getCastType($key);

        if (null === $value) {
            return null;
        }

        switch ($castType) {
            case 'int':
                $value = $this->toInt($value);
                break;
            case 'float':
                $value = $this->fromFloat($value);
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'bool':
            case 'boolean':
                $value = $this->toBool($value);
                break;
            case 'date':
                $value = $this->asDate($value);
                break;
            case TriStateService::TYPE_COERCED:
            case TriStateService::TYPE_STRICT:
            case TriStateService::TYPE_STRING:
                $value = $this->resolveTriStateValue($castType, $value);
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
     * @param  string $key
     *
     * @return string
     */
    protected function convertDeprecatedKey(string $key): string
    {
        if (! $this->isDeprecated($key)) {
            return $key;
        }

        $newKey = $this->deprecated[$key];

        $this->logDeprecationWarning($key, $newKey);

        return $newKey;
    }

    /**
     * @param  array    $attributes
     * @param  null|int $flags
     *
     * @return array
     */
    protected function createArrayFromAttributes(array $attributes, ?int $flags): array
    {
        $mutatedAttributes = $this->getMutatedAttributes();

        $addedAttributes = $this->addMutatedAttributesToArray($attributes, $mutatedAttributes, $flags);

        return $this->addCastAttributesToArray($addedAttributes, $mutatedAttributes, $flags);
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
     * @param  string $string
     *
     * @return mixed
     */
    protected function getCastAttributeValue(string $string)
    {
        return $this->castAttribute($string, $this->attributes[$string]);
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string $key
     *
     * @return null|string
     */
    protected function getCastType(string $key): ?string
    {
        $casts         = $this->getCasts();
        $normalizedKey = Str::changeCase($key);

        return $casts[$normalizedKey];
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    protected function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * Cast the given attribute using a custom cast class.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function getClassCastableAttributeValue(string $key, $value)
    {
        if (isset($this->classCastCache[$key])) {
            return $this->classCastCache[$key];
        }

        $value = $this->getCastModel($key, $value);

        if (is_object($value)) {
            $this->classCastCache[$key] = $value;
        } else {
            unset($this->classCastCache[$key]);
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function getDateFormats(): array
    {
        return Pdk::get('dateFormats');
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
        $castType = $this->parseCasterClass($this->getCastType($key));

        if (! $castType || in_array($castType, self::$primitiveCastTypes, true)) {
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
    protected function isDeprecated(string $key): bool
    {
        return array_key_exists($key, $this->deprecated);
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    protected function isGuarded(string $key): bool
    {
        return array_key_exists($key, $this->guarded);
    }

    /**
     * @param  string $key
     * @param  string $newKey
     *
     * @return void
     */
    protected function logDeprecationWarning(string $key, string $newKey): void
    {
        Logger::deprecated("Attribute '$key'", "'$newKey'", ['class' => static::class]);
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
     * @param  null|string $class
     *
     * @return string
     */
    protected function parseCasterClass(?string $class): ?string
    {
        return Str::contains($class ?? '', ':')
            ? explode(':', $class, 2)[0]
            : $class;
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
        return $date->format(Pdk::get('defaultDateFormat'));
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
            $value = $this->mutateAttribute($key, $value);
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
     */
    private function getCastModel(string $key, $value)
    {
        $class = $this->getCasts()[$key];

        if (is_a($value, $class)) {
            return $value;
        }

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
            $exception = new InvalidCastException($key, $class, $arguments, $e);

            Logger::error($exception->getMessage(), ['exception' => $exception]);

            return null;
        }
    }

    /**
     * @param  mixed $value
     *
     * @return bool
     */
    private function isStringBoolean($value): bool
    {
        return in_array($value, ['true', 'false'], true);
    }

    /**
     * @param  string $castType
     * @param  mixed  $value
     *
     * @return mixed
     */
    private function resolveTriStateValue(string $castType, $value)
    {
        $service = Pdk::get(TriStateServiceInterface::class);

        switch ($castType) {
            case TriStateService::TYPE_COERCED:
                $value = $service->coerce($value);
                break;

            case TriStateService::TYPE_STRICT:
                $value = $service->cast($value);
                break;

            case TriStateService::TYPE_STRING:
                $value = $service->coerceString($value);
                break;
        }

        return $value;
    }

    /**
     * @param  mixed $value
     *
     * @return bool
     */
    private function toBool($value): bool
    {
        if ($this->isStringBoolean($value)) {
            return 'true' === $value;
        }

        return (bool) $value;
    }

    /**
     * @param  mixed $value
     *
     * @return int
     */
    private function toInt($value): int
    {
        if ($this->isStringBoolean($value)) {
            return (int) $this->toBool($value);
        }

        return (int) $value;
    }
}
