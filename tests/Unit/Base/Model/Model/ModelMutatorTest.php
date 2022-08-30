<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

it('supports get mutators', function () {
    $model = new MockMutateModel(['bloemkool' => 'random']);
    expect($model->bloemkool)->toEqual('bloemkool');
});

it('supports set mutators', function () {
    $model = new MockMutateModel(['perenboom' => 'random']);
    expect($model->perenboom)->toEqual('mutated_random');
});
