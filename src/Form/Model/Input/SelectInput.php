<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Form\Collection\SelectOptionsCollection;

/**
 * @property string                                                  $description
 * @property string                                                  $element
 * @property string                                                  $label
 * @property string                                                  $name
 * @property \MyParcelNL\Pdk\Form\Collection\SelectOptionsCollection $options
 * @property string                                                  $type
 */
class SelectInput extends BaseInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['options'] = SelectOptionsCollection::class;

        $this->casts['options'] = SelectOptionsCollection::class;

        parent::__construct($data);
    }
}
