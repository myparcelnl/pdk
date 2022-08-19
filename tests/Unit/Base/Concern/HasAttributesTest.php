<?php
/** @noinspection PhpUnused,PhpUndefinedMethodInspection,PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,PhpMultipleClassesDeclarationsInOneFile,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Mocks\ClassWithGuardedAttributes;
use MyParcelNL\Pdk\Tests\Mocks\MockCastingModel;
use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;
use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

uses()->group('model');

// todo of test guarded hier

it('casts attributes to classes', function () {
    $model = new MockCastingModel();

    expect($model->collection)
        ->toBeInstanceOf(Collection::class)
        ->and($model->object)
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model->date)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->and($model->datetime)
        ->toBeInstanceOf(DateTimeImmutable::class);
});

it('casts attributes to primitives', function ($property, $assertion) {
    $model = new MockCastingModel();

    expect($model[$property])->{$assertion}();
})->with([
    'String to int'   => ['stringInt', 'toBeInt'],
    'String to bool'  => ['stringBool', 'toBeBool'],
    'Int to string'   => ['intString', 'toBeString'],
    'Int to float'    => ['intFloat', 'toBeFloat'],
    'String to float' => ['stringFloat', 'toBeFloat'],
]);

it('casts everything properly to array', function () {
    $model = new MockCastingModel();

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

it('can use casted properties', function () {
    $model = new MockCastingModel();

    $model->object->property = 'pen';

    expect($model->object)
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model['object'])
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model->getObject())
        ->toBeInstanceOf(MockCastModel::class);
});

it('throws error on invalid cast', function () {
    class InvalidCastingModel extends Model
    {
        protected $attributes = ['value' => null];

        protected $casts      = ['value' => MockCastModel::class];
    }

    $model = new InvalidCastingModel([
        'value' => new DateTime(),
    ]);

    $model->toArray();
})->throws(InvalidCastException::class);

it('gets only requested fields', function () {
    $model = new MockMutateModel();

    expect($model->only(['bloemkool', 'perenboom']))->toHaveKeys(['bloemkool', 'perenboom']);
});

it('gets only requested fields with string', function () {
    $model = new MockMutateModel();

    expect($model->only('myProperty'))->toHaveKeys(['myProperty']);
});

it('checks if guarded properties cannot be modified', function () {
    $model = new ClassWithGuardedAttributes(['field' => 1]);

    $model['field'] = 2;
    $model->setField(3);
    $model->fill(['field' => 4]);
    $model->field = 'test';

    expect($model->field)->toEqual('test');
});
