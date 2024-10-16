<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

class MockNestedModel extends Model
{
    public    $attributes = [
        'myValue' => null,
        'myModel' => null,
    ];

    protected $casts      = [
        'myValue' => 'string',
        'myModel' => MockNestedModel::class,
    ];
}
