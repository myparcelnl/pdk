<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

/**
 * @property string $type
 * @property bool   $isBool
 * @property string $label
 * @property string $name
 * @property string $description
 * @property array  $values
 */
class ToggleInput extends AbstractInput
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
                'label' => 'Yes',
            ],
            [
                'id'    => 'off',
                'value' => 0,
                'label' => 'No',
            ],
        ];

        $this->casts['isBool'] = 'bool';
        $this->casts['values'] = 'array';

        parent::__construct($data);
    }
}
