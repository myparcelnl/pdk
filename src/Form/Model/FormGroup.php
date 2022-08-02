<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $name
 */
class FormGroup extends Model
{
    protected $attributes = [
        'name' => null,
    ];

    protected $casts      = [
        'name' => 'string',
    ];
}
