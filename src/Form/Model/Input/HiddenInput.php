<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Settings\Model\View\CarrierSettingsView;

/**
 * @property string $type
 * @property string $name
 */
class HiddenInput extends AbstractInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['type'] = CarrierSettingsView::INPUT_HIDDEN;

        $this->casts['type'] = 'string';

        parent::__construct($data);
    }
}
