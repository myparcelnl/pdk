<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input\Select;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;

/**
 * @property string $type
 * @property string $label
 * @property string $name
 * @property string $description
 * @property array  $options
 */
class CountrySelectInput extends SelectInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['options'] = CountryCodes::ALL;
        $this->casts['options']      = 'array';

        parent::__construct($data);
    }
}
