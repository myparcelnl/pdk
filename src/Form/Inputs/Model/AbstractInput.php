<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Inputs\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $name
 * @property string $label
 * @property string $desc
 */
class AbstractInput extends Model
{
    protected $attributes = [
        'name'  => null,
        'label' => null,
        'desc'  => null,
    ];

    protected $casts      = [
        'name'  => 'string',
        'label' => 'string',
        'desc'  => 'string',
    ];
}
