<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

/**
 * @property string $description
 * @property string $element
 * @property string $label
 * @property string $name
 * @property string $type
 */
class TextInput extends BaseInput
{
    protected $guarded = [
        'type' => 'text',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->attributes['type'] = $this->guarded['type'];
        $this->casts['type']      = 'string';

        parent::__construct($data);
    }
}
