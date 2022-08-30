<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

it('can use property accessors', function () {
    $model             = new MockMutateModel();
    $model->myProperty = 2;

    expect($model->myProperty)->toBe(2);
});

it('can use getters and setters', function () {
    $model = new MockMutateModel();
    $model->setMyProperty(3);

    expect($model->getMyProperty())->toBe(3);
});

it('can use offset getters and setters', function () {
    $model               = new MockMutateModel();
    $model['myProperty'] = 2;

    expect($model['myProperty'])->toBe(2);
});
