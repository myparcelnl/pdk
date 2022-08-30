<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Model\Input;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;

/**
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $description
 */
class AbstractInput extends Model
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->guarded['type'] = Utils::classBasename(static::class);

        $this->attributes['type']        = null;
        $this->attributes['name']        = null;
        $this->attributes['label']       = null;
        $this->attributes['description'] = null;

        $this->casts['name']        = 'string';
        $this->casts['label']       = 'string';
        $this->casts['description'] = 'string';

        parent::__construct($data);
    }
}
