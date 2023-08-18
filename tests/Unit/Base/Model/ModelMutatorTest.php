<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Mock\Model\MockMutateModel;

it('supports get mutators', function () {
    $model = new MockMutateModel(['bloemkool' => 'random']);
    expect($model->bloemkool)->toEqual('bloemkool');
});

it('supports set mutators', function () {
    $model = new MockMutateModel(['perenboom' => 'random']);
    expect($model->perenboom)->toEqual('mutated_random');
});
