<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class GuardedAttributesModel extends Model
{
    protected $attributes = ['field' => 'test'];

    protected $guarded    = ['field' => 'test'];
}
