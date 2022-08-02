<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Inputs\Model;

use MyParcelNL\Pdk\Form\Model\FormGroup;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

/**
 * @property string                               $type
 * @property bool                                 $multiple
 * @property string                               $label
 * @property string                               $name
 * @property string                               $desc
 * @property \MyParcelNL\Pdk\Form\Model\FormGroup $formGroupClass
 */
class CheckboxInput extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type']           = CarrierSettingsView::INPUT_CHECKBOX;
        $this->attributes['multiple']       = true;
        $this->attributes['formGroupClass'] = FormGroup::class;

        $this->casts['type']           = 'string';
        $this->casts['multiple']       = 'bool';
        $this->casts['formGroupClass'] = FormGroup::class;

        parent::__construct($data);
    }
}
