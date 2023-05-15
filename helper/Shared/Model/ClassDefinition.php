<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Helper\Shared\Collection\ClassMethodCollection;
use MyParcelNL\Pdk\Helper\Shared\Collection\ClassPropertyCollection;
use MyParcelNL\Pdk\Helper\Shared\Collection\KeyValueCollection;
use MyParcelNL\Pdk\Helper\Shared\Collection\TypeCollection;
use ReflectionClass;

/**
 * @property KeyValueCollection        $comments
 * @property ClassMethodCollection     $methods
 * @property ClassDefinitionCollection $parents
 * @property ClassPropertyCollection   $properties
 * @property ReflectionClass           $ref
 * @property TypeCollection            $types
 */
class ClassDefinition extends Model
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
}
