<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Inputs\Model;

use MyParcelNL\Pdk\Form\Model\FormGroup;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

/**
 * @property string                               $type
 * @property bool                                 $isBool
 * @property string                               $label
 * @property string                               $name
 * @property string                               $desc
 * @property \MyParcelNL\Pdk\Form\Model\FormGroup $formGroupClass
 * @property array                                $values
 */
class ToggleInput extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type']           = CarrierSettingsView::INPUT_TOGGLE;
        $this->attributes['isBool']         = null;
        $this->attributes['formGroupClass'] = FormGroup::class;
        $this->attributes['values']         = [
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

        $this->casts['type']           = 'string';
        $this->casts['isBool']         = 'bool';
        $this->casts['formGroupClass'] = FormGroup::class;
        $this->casts['values']         = 'array';

        parent::__construct($data);
    }
}
