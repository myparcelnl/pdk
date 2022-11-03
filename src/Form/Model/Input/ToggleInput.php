<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

/**
 * @property string $description
 * @property string $element
 * @property bool   $isBool
 * @property string $label
 * @property string $name
 * @property string $type
 * @property array  $values
 */
class ToggleInput extends BaseInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['isBool'] = true;
        $this->attributes['values'] = [
            [
                'id'    => 'on',
                'value' => 1,
                'label' => 'input_toggle_on',
            ],
            [
                'id'    => 'off',
                'value' => 0,
                'label' => 'input_toggle_off',
            ],
        ];

        $this->casts['isBool'] = 'bool';
        $this->casts['values'] = 'array';

        parent::__construct($data);
    }
}
