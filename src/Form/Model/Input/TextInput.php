<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

/**
 * @property string $type
 * @property string $label
 * @property string $desc
 * @property string $name
 */
class TextInput extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type'] = CarrierSettingsView::INPUT_TEXT;

        $this->casts['type'] = 'string';

        parent::__construct($data);
    }
}
