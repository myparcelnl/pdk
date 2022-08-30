<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $name
 * @property string $label
 * @property array  $options
 */
class SelectOptions extends Model
{
    protected $attributes = [
        'name'    => null,
        'label'   => null,
        'options' => [],
    ];

    protected $casts      = [
        'name'    => 'string',
        'label'   => 'string',
        'options' => 'array',
    ];
}
