<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput          $defaultForm
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput            $defaultCustomsCode
 * @property \MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect $defaultCountryOrigin
 */
class CustomsSettingsView extends Model
{
    public function __construct(array $data = null)
    {
        $this->attributes['defaultForm']          = [
            'name'    => 'defaultForm',
            'type'    => 'select',
            'label'   => 'Lorem',
            'desc'    => 'Lorem',
            'options' => [],
        ];
        $this->attributes['defaultCustomsCode']   = [
            'name'  => 'defaultCustomsCode',
            'type'  => 'text',
            'label' => 'Default customs code',
            'desc'  => 'Lorem Ipsum',
        ];
        $this->attributes['defaultCountryOrigin'] = [
            'name'    => 'defaultCountryOrigin',
            'type'    => 'select',
            'label'   => 'Lorem',
            'desc'    => 'Lorem',
            'options' => CountryCodes::CC_LIST,
        ];

        $this->casts['defaultForm']          = SelectInput::class;
        $this->casts['defaultCustomsCode']   = TextInput::class;
        $this->casts['defaultCountryOrigin'] = CountrySelect::class;

        parent::__construct($data);
    }
}
