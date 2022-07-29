<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;

class MockCastingModel extends Model
{
    protected $attributes = [
        'collection'     => [
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
        ],
        'object'         => ['property' => 'hello'],
        'date'           => '2022-01-10',
        'datetime'       => '2022-01-10 14:03:00',
        'timestamp'      => '2022-01-10 14:03:00',
        'string_int'     => '4',
        'string_bool'    => 'true',
        'int_string'     => 1234,
        'int_float'      => 2,
        'string_float'   => '2',
        'without_a_cast' => 'whatever',
        'null'           => null,
    ];

    protected $casts      = [
        'collection'   => Collection::class,
        'object'       => MockCastModel::class,
        'date'         => 'date',
        'datetime'     => 'datetime',
        'timestamp'    => 'timestamp',
        'string_int'   => 'int',
        'string_bool'  => 'bool',
        'int_string'   => 'string',
        'int_float'    => 'float',
        'string_float' => 'float',
        'null'         => 'string',
    ];
}
