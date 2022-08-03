<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Helpers;

use MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Settings\Model\View\CustomsSettingsView;

class FormBuilder
{
    /**
     * @return \MyParcelNL\Pdk\Settings\Model\View\CustomsSettingsView
     */
    public function getCustomsView(): CustomsSettingsView
    {
        return new CustomsSettingsView();
    }
}
