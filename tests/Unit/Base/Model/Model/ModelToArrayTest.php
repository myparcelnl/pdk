<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

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
