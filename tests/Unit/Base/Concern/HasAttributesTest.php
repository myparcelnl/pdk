<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,PhpMultipleClassesDeclarationsInOneFile,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Model\InvalidCastException;
use MyParcelNL\Pdk\Base\Model\Model;

class CastModel extends Model
{
    protected $attributes = ['property' => null];
}

class CastingModel extends Model
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
        'object'       => CastModel::class,
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

it('casts attributes to classes', function () {
    $model = new CastingModel();

    expect($model->collection)
        ->toBeInstanceOf(Collection::class)
        ->and($model->object)
        ->toBeInstanceOf(CastModel::class)
        ->and($model->date)
        ->toBeInstanceOf(DateTime::class)
        ->and($model->datetime)
        ->toBeInstanceOf(DateTime::class);
});

it('casts attributes to primitives', function ($property, $assertion) {
    $model = new CastingModel();

    expect($model[$property])->{$assertion}();
})->with([
    'String to int'   => ['stringInt', 'toBeInt'],
    'String to bool'  => ['stringBool', 'toBeBool'],
    'Int to string'   => ['intString', 'toBeString'],
    'Int to float'    => ['intFloat', 'toBeFloat'],
    'String to float' => ['stringFloat', 'toBeFloat'],
]);

it('casts everything properly to array', function () {
    $model = new CastingModel();

    expect($model->attributesToArray())->toBe([
        'collection'   => [
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
        ],
        'object'       => [
            'property' => 'hello',
        ],
        'date'         => '2022-01-10 00:00:00',
        'datetime'     => '2022-01-10 14:03:00',
        'timestamp'    => 1641823380,
        'stringInt'    => 4,
        'stringBool'   => true,
        'intString'    => '1234',
        'intFloat'     => 2.0,
        'stringFloat'  => 2.0,
        'withoutACast' => 'whatever',
        'null'         => null,
    ]);
});

it('throws error on invalid cast', function () {
    class InvalidCastingModel extends Model
    {
        protected $attributes = ['value' => null];

        protected $casts      = ['value' => CastModel::class];
    }

    $model = new InvalidCastingModel([
        'value' => new DateTime(),
    ]);

    $model->toArray();
})->throws(InvalidCastException::class);
