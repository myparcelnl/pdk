<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput          $defaultForm
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput            $defaultCustomsOrigin
 * @property \MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelect $defaultCountryOrigin
 */
class CustomsSettingsView extends Model
{
    protected $attributes = [
        'defaultForm'          => SelectInput::class,
        'defaultCustomsCode'   => TextInput::class,
        'defaultCountryOrigin' => CountrySelect::class,
    ];

    protected $casts      = [
        'defaultForm'          => SelectInput::class,
        'defaultCustomsCode'   => TextInput::class,
        'defaultCountryOrigin' => CountrySelect::class,
    ];

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->attributes = $data;
        }

        parent::__construct($data);
    }
}
