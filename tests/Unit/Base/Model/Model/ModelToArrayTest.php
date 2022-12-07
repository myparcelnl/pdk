<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;
use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

it('can use toArray', function () {
    expect((new MockMutateModel())->toArray())->toEqual([
        'myProperty' => 1,
        'perenboom'  => 'mutated_',
        'bloemkool'  => 'bloemkool',
    ]);
});

it('can use toSnakeCaseArray', function () {
    expect((new MockMutateModel())->toSnakeCaseArray())->toEqual([
        'my_property' => 1,
        'perenboom'   => 'mutated_',
        'bloemkool'   => 'bloemkool',
    ]);
});

it('can use toKebabCaseArray', function () {
    expect((new MockMutateModel())->toKebabCaseArray())->toEqual([
        'my-property' => 1,
        'perenboom'   => 'mutated_',
        'bloemkool'   => 'bloemkool',
    ]);
});

it('can use toStudlyCaseArray', function () {
    expect((new MockMutateModel())->toStudlyCaseArray())->toEqual([
        'MyProperty' => 1,
        'Perenboom'  => 'mutated_',
        'Bloemkool'  => 'bloemkool',
    ]);
});

it('can use toArrayWithoutNull', function () {
    expect((new MockCastModel())->toArrayWithoutNull())->toEqual([]);
});
