<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input\Select;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;

/**
 * @property string                                 $type
 * @property string                                 $label
 * @property string                                 $name
 * @property \MyParcelNL\Pdk\Base\Data\CountryCodes $options
 */
class CountrySelect extends SelectInput
{
    /**
     * @param  array $data
     */
    public function __construct(array $data = [])
    {
        $this->attributes['options'] = CountryCodes::CC_LIST;

        $this->casts['options'] = CountryCodes::class;

        parent::__construct($data);
    }
}
