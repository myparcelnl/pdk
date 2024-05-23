<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property mixed $snakeCase
 * @property mixed $camelCase
 * @property mixed $studlyCase
 */
class DifferentAttributeCasingModel extends Model
{
    protected $attributes = [
        'snakeCase'  => null,
        'camelCase'  => null,
        'studlyCase' => null,
    ];
}

