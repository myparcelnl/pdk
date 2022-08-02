<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Inputs\Model;

use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;

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
