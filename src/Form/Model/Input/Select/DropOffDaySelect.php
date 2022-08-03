<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input\Select;

use MyParcelNL\Pdk\Form\Model\Input\AbstractInput;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

/**
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $desc
 */
class DropOffDaySelect extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type']     = CarrierSettingsView::INPUT_DATE_SELECT;
        $this->attributes['multiple'] = null;
        $this->attributes['values']   = null;

        $this->casts['type']     = 'string';
        $this->casts['multiple'] = 'bool';
        $this->casts['values']   = 'array';

        parent::__construct($data);
    }
}
