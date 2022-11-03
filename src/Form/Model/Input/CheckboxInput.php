<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

/**
 * @property string $description
 * @property string $element
 * @property string $label
 * @property string $name
 * @property string $type
 */
class CheckboxInput extends BaseInput
{
    protected $guarded = [
        'type' => 'checkbox',
    ];
}
