<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Mock\Model\MockMutateModel;

it('can use isset on property', function () {
    $model = new MockMutateModel();

    expect(isset($model->myProperty))
        ->toBeTrue()
        ->and(isset($model->otherProperty))
        ->toBeFalse();
});

it('can use isset on array offset', function () {
    $model = new MockMutateModel();

    expect(isset($model['myProperty']))
        ->toBeTrue()
        ->and(isset($model['otherProperty']))
        ->toBeFalse();
});

it('can use unset on array offset', function () {
    $model = new MockMutateModel();
    unset($model['myProperty']);

    expect(isset($model['myProperty']))
        ->toBeFalse();
});
