<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class MockNestedModel extends Model
{
    public    $attributes = [
        'my_value' => null,
        'myModel'  => null,
    ];

    protected $casts      = [
        'my_value' => 'string',
        'myModel'  => MockNestedModel::class,
    ];
}
