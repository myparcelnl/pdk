<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input\Select;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;

/**
 * @property string                                 $type
 * @property string                                 $label
 * @property string                                 $name
 * @property string                                 $description
 * @property \MyParcelNL\Pdk\Base\Data\CountryCodes $options
 */
class CountrySelectInput extends SelectInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['options'] = CountryCodes::ALL;

        $this->casts['options'] = CountryCodes::class;

        parent::__construct($data);
    }
}
