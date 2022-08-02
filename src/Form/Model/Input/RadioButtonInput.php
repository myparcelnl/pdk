<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Form\Collection\InputOptionsCollection;

/**
 * @property string                                  $name
 * @property string                                  $label
 * @property string                                  $description
 * @property string                                  $type
 * @property bool                                    $multiple
 * @property \MyParcelNL\Pdk\Form\Model\InputOptions $options
 */
class RadioButtonInput extends AbstractInput
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
