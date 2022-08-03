<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Form\Collection\SelectOptionsCollection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

/**
 * @property string                                                  $type
 * @property string                                                  $label
 * @property string                                                  $name
 * @property \MyParcelNL\Pdk\Form\Collection\SelectOptionsCollection $options
 */
class SelectInput extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type']    = CarrierSettingsView::INPUT_SELECT;
        $this->attributes['options'] = SelectOptionsCollection::class;

        $this->casts['type']    = 'string';
        $this->casts['options'] = SelectOptionsCollection::class;

        parent::__construct($data);
    }
}
