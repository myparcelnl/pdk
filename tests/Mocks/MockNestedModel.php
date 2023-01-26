<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

class MockNestedModel extends Model
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
