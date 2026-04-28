<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use InvalidArgumentException;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;

it('can be converted to array', function () {
    $operation = new FormSetValueOperation(new FormOperationBuilder(), 'value');

    expect($operation->toArray())->toBe([
        '$setValue' => [
            '$value' => 'value',
        ],
    ]);
});

it('preserves a falsy $value when converted to array', function ($falsyValue) {
    $operation = new FormSetValueOperation(new FormOperationBuilder(), $falsyValue);

    expect($operation->toArray())->toBe([
        '$setValue' => [
            '$value' => $falsyValue,
        ],
    ]);
})->with([
    'false'        => [false],
    'integer zero' => [0],
    'empty string' => [''],
]);

it('throws error when a non-scalar value is passed', function () {
    new FormSetValueOperation(new FormOperationBuilder(), (object) ['something' => 1]);
})->throws(InvalidArgumentException::class);
