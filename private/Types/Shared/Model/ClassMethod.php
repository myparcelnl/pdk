<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Model;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use ReflectionMethod;

/**
 * @property string            $name
 * @property \ReflectionMethod $ref
 * @property array             $parameters
 */
class ClassMethod extends Model implements StorableArrayable
{
    public    $attributes = [
        'name'       => null,
        'ref'        => null,
        'parameters' => [],
    ];

    protected $casts      = [
        'name'       => 'string',
        'ref'        => ReflectionMethod::class,
        'parameters' => 'array',
    ];

    /**
     * @return \ReflectionMethod
     * @throws \ReflectionException
     * @noinspection PhpUnused
     */
    public function getRefAttribute(): ReflectionMethod
    {
        if ($this->attributes['ref'] instanceof ReflectionMethod) {
            return $this->attributes['ref'];
        }

        return new ReflectionMethod($this->attributes['ref']['class'], $this->attributes['ref']['name']);
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        $reflectionMethod = $this->ref;
        $reflectionClass  = $reflectionMethod->getDeclaringClass();

        return [
            'name'       => $this->name,
            'parameters' => $this->parameters,
            'ref'        => [
                'class' => $reflectionClass->getName(),
                'name'  => $reflectionMethod->getName(),
            ],
        ];
    }
}
