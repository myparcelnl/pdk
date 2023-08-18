<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class DifferentAttributeCasingModel extends Model
{
    protected $attributes = [
        'snakeCase'  => null,
        'camelCase'  => null,
        'studlyCase' => null,
    ];
}

