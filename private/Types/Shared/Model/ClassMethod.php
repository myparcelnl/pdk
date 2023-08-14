<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use ReflectionMethod;

/**
 * @property string            $name
 * @property \ReflectionMethod $ref
 * @property array             $parameters
 */
class ClassMethod extends Model
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
}
