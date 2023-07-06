<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection, PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;

it('fills attributes', function () {
    $model = new MockCastModel();
    $model->fill(['property' => 'poes']);

    expect($model->property)->toBe('poes');
});

it('ignores extraneous attributes', function () {
    $model = new MockCastModel();
    $model->fill(['property' => 'poes', 'extra' => 'extra']);

    expect($model->toArray())->toBe(['property' => 'poes']);
});