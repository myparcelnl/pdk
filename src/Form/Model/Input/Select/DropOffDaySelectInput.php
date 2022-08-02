<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input\Select;

use MyParcelNL\Pdk\Form\Model\Input\AbstractInput;

/**
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $description
 */
class DropOffDaySelectInput extends AbstractInput
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['multiple'] = false;
        $this->attributes['values']   = [];

        $this->casts['multiple'] = 'bool';
        $this->casts['values']   = 'array';

        parent::__construct($data);
    }
}
