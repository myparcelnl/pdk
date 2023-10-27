<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassMethodCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassPropertyCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\KeyValueCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\TypeCollection;
use ReflectionClass;
use RuntimeException;

/**
 * @property KeyValueCollection        $comments
 * @property ClassMethodCollection     $methods
 * @property ClassDefinitionCollection $parents
 * @property ClassPropertyCollection   $properties
 * @property ReflectionClass           $ref
 * @property TypeCollection            $types
 */
final class ClassDefinition extends Model
{
    public    $attributes = [
        'comments'   => KeyValueCollection::class,
        'methods'    => ClassMethodCollection::class,
        'parents'    => ClassDefinitionCollection::class,
        'properties' => ClassPropertyCollection::class,
        'ref'        => null,
        'types'      => TypeCollection::class,
    ];

    protected $casts      = [
        'comments'   => KeyValueCollection::class,
        'methods'    => ClassMethodCollection::class,
        'parents'    => ClassDefinitionCollection::class,
        'properties' => ClassPropertyCollection::class,
        'ref'        => ReflectionClass::class,
        'types'      => TypeCollection::class,
    ];

    /**
     * @return null|string
     */
    public function getCollectionValueType(): ?string
    {
        if (! $this->isSubclassOf(Collection::class)) {
            throw new RuntimeException(sprintf('ref is not a %s', Utils::classBasename(Collection::class)));
        }

        $itemsProperty = $this->properties->firstWhere('name', 'items');

        if (! $itemsProperty || $itemsProperty->types->isEmpty()) {
            return null;
        }

        /** @var \Symfony\Component\PropertyInfo\Type $type */
        $type = $itemsProperty->types->first();

        $valueType = Arr::first($type->getCollectionValueTypes());

        return $valueType ? $valueType->getClassName() : null;
    }

    /**
     * @return \ReflectionClass
     * @throws \ReflectionException
     * @noinspection PhpUnused
     */
    public function getRefAttribute(): ReflectionClass
    {
        if ($this->attributes['ref'] instanceof ReflectionClass) {
            return $this->attributes['ref'];
        }

        return new ReflectionClass($this->attributes['ref']['name']);
    }

    /**
     * @param  string $className
     *
     * @return bool
     */
    public function isSubclassOf(string $className): bool
    {
        return $this->parents->containsStrict(function (ClassDefinition $definition) use ($className) {
            return $definition->ref->isSubclassOf($className) || $definition->ref->getName() === $className;
        });
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return [
            'ref'        => [
                'name' => $this->ref->getName(),
            ],
            'comments'   => $this->comments->toStorableArray(),
            'methods'    => $this->methods->toStorableArray(),
            'parents'    => $this->parents->toStorableArray(),
            'properties' => $this->properties->toStorableArray(),
            'types'      => $this->types->toStorableArray(),
        ];
    }
}
