<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
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
     */
    public static function cacheMutatedAttributes(string $class): void
    {
        static::$mutatorCache[$class] = (new Collection(static::getMutatorMethods($class)))
            ->map(fn($match) => Str::camel($match))
            ->all();
    }

    /**
     * Get all the attribute mutator methods.
     */
    protected static function getMutatorMethods(mixed $class): array
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[1];
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @param  null|int $flags
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function except($attributes, ?int $flags = null): array
    {
        return $this->createArrayFromAttributes(Arr::except($this->attributes, Arr::wrap($attributes)), $flags);
    }

    /**
     * Decode the given float.
     */
    public function fromFloat(mixed $value): float
    {
        return match ((string) $value) {
            'Infinity' => INF,
            '-Infinity' => -INF,
            'NaN' => NAN,
            default => (float) $value,
        };
    }

    /**
     * Decode the given JSON back into an array or object.
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
     */
    public function getAttributes(?int $flags = null): array
    {
        $attributes = $this->attributes;

        if ($flags) {
            if ($flags & Arrayable::SKIP_NULL) {
                $attributes = array_filter($attributes, static fn($value) => null !== $value);
            }

            if ($flags & Arrayable::CASE_SNAKE || $flags & Arrayable::CASE_KEBAB || $flags & Arrayable::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($this->attributes, $this->getFlagCase($flags));
            }
        }

        return $attributes;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, $this->createMutatorName('get', $key));
    }

    /**
     * Determine if a set mutator exists for an attribute.
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function only($attributes, ?int $flags = null): array
    {
        return $this->createArrayFromAttributes(Arr::only($this->attributes, Arr::wrap($attributes)), $flags);
    }

    /**
     * Set a given attribute on the model.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $key = $this->convertDeprecatedKey($this->convertAttributeCase($key));

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
     * @param  null|int $flags
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes, ?int $flags): array
    {
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

            if ($attributes[$key] instanceof DateTimeInterface && $this->isClassCastable($originalKey)) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] instanceof Arrayable) {
                $attributes[$key] = $attributes[$key]->toArray($flags);
            }

            if ($flags & Arrayable::SKIP_NULL && null === $attributes[$key]) {
                unset($attributes[$key]);
            }

            if ($flags & Arrayable::CASE_SNAKE || $flags & Arrayable::CASE_KEBAB || $flags & Arrayable::CASE_STUDLY) {
                $attributes = Utils::changeArrayKeysCase($attributes, $this->getFlagCase($flags));
            }
        }

        return $attributes;
    }

    /**
     * @param  null|int $flags
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
     * @throws \Exception
     */
    protected function asDate(mixed $value): DateTimeImmutable
    {
        return $this->asDateTime($value)
            ->setTime(0, 0);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  \DateTimeInterface|string|array{date: string, timezone: string, timezone_type: int} $value
     *
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

        if (is_array($value) && isset($value['date'])) {
            return new DateTimeImmutable($value['date'], new DateTimeZone($value['timezone']));
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
     * @throws \Exception
     */
    protected function asTimestamp(mixed $value): int
    {
        return $this->asDateTime($value)
            ->getTimestamp();
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \Exception
     */
    protected function castAttribute(string $key, mixed $value)
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
     * @param  null|int $flags
     */
    protected function convertAttributeCase(string $key, ?int $flags = null): string
    {
        $case = $this->getFlagCase($flags);

        return Str::{$case}($key);
    }

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
     * @param  null|int $flags
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function createArrayFromAttributes(array $attributes, ?int $flags): array
    {
        $mutatedAttributes = $this->getMutatedAttributes();

        $attributes = $this->addMutatedAttributesToArray($attributes, $mutatedAttributes, $flags);

        return $this->addCastAttributesToArray($attributes, $mutatedAttributes, $flags);
    }

    /**
     * Get an attribute from the $attributes array.
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
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getAttributeValue(string $key)
    {
        return $this->transformModelValue($key, $this->getAttributeFromArray($key));
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getCastAttribute(string $string)
    {
        return $this->castAttribute($string, $this->attributes[$string]);
    }

    /**
     * Get the type of cast for a model attribute.
     */
    protected function getCastType(string $key): ?string
    {
        return $this->getCasts()[$this->convertAttributeCase($key)];
    }

    /**
     * Get the casts array.
     */
    protected function getCasts(): array
    {
        return Utils::changeArrayKeysCase($this->casts);
    }

    /**
     * Cast the given attribute using a custom cast class.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getClassCastableAttributeValue(string $key, mixed $value)
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

    protected function getDateFormats(): array
    {
        return Pdk::get('dateFormats');
    }

    /**
     * @param  null|int $flags
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
     * @param  array|string|null $types
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
     */
    protected function isClassCastable(string $key): bool
    {
        $castType = $this->parseCasterClass($this->getCastType($key));

        if (! $castType || in_array($castType, self::$primitiveCastTypes)) {
            return false;
        }

        if (class_exists($castType)) {
            return true;
        }

        return false;
    }

    protected function isDeprecated(string $key): bool
    {
        return array_key_exists($key, $this->deprecated);
    }

    protected function isGuarded(string $key): bool
    {
        return array_key_exists($key, $this->guarded);
    }

    protected function logDeprecationWarning(string $key, string $newKey): void
    {
        Logger::warning(
            "[DEPRECATION] Attribute '$key' is deprecated. Use '$newKey' instead.",
            ['class' => static::class]
        );
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @return mixed
     */
    protected function mutateAttribute(string $key, mixed $value)
    {
        return $this->{$this->createMutatorName('get', $key)}($value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @return mixed
     */
    protected function mutateAttributeForArray(string $key, mixed $value)
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
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(Pdk::get('defaultDateFormat'));
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @return mixed
     */
    protected function setMutatedAttributeValue(string $key, mixed $value)
    {
        return $this->{$this->createMutatorName('set', $key)}($value);
    }

    /**
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function transformModelValue(string $key, mixed $value)
    {
        if ($this->hasGetMutator($key)) {
            $value = $this->mutateAttribute($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    private function createMutatorName(string $type, string $key): string
    {
        return sprintf('%s%sAttribute', $type, Str::studly($key));
    }

    /**
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getCastModel(string $key, mixed $value)
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
            throw new InvalidCastException($key, $class, $arguments, $e);
        }
    }

    private function isStringBoolean(mixed $value): bool
    {
        return in_array($value, ['true', 'false'], true);
    }

    /**
     * @return mixed
     */
    private function resolveTriStateValue(string $castType, mixed $value)
    {
        $service = Pdk::get(TriStateServiceInterface::class);

        return match ($castType) {
            TriStateService::TYPE_COERCED => $service->coerce($value),
            TriStateService::TYPE_STRICT => $service->cast($value),
            TriStateService::TYPE_STRING => empty($value) ? '' : (string) $value,
            default => $value,
        };
    }

    private function toBool(mixed $value): bool
    {
        if ($this->isStringBoolean($value)) {
            return 'true' === $value;
        }

        return (bool) $value;
    }

    private function toInt(mixed $value): int
    {
        if ($this->isStringBoolean($value)) {
            return (int) $this->toBool($value);
        }

        return (int) $value;
    }
}
