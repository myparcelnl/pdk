<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property array  $query
 * @property int    $id
 * @property string $name
 */
class InputOptions extends Model
{
    protected $attributes = [
        'id'   => null,
        'name' => null,
    ];

    protected $casts      = [
        'id'   => 'string',
        'name' => 'string',
    ];
}
