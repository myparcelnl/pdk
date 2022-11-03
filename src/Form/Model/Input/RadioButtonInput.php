<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Form\Collection\InputOptionsCollection;

/**
 * @property string                                  $description
 * @property string                                  $element
 * @property string                                  $label
 * @property bool                                    $multiple
 * @property string                                  $name
 * @property \MyParcelNL\Pdk\Form\Model\InputOptions $options
 */
class RadioButtonInput extends BaseInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['multiple'] = true;
        $this->attributes['options']  = InputOptionsCollection::class;

        $this->casts['multiple'] = 'bool';
        $this->casts['options']  = InputOptionsCollection::class;

        parent::__construct($data);
    }
}
