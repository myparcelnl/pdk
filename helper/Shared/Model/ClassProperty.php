<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Helper\Shared\Collection\TypeCollection;

/**
 * @property string         $name
 * @property array          $parameters
 * @property TypeCollection $types
 */
class ClassProperty extends Model
{
    public    $attributes = [
        'name'  => null,
        'types' => TypeCollection::class,
    ];

    protected $casts      = [
        'name'  => 'string',
        'types' => TypeCollection::class,
    ];
}

