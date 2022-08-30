<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

it('can use constructor arguments', function () {
    $model = new MockMutateModel([
        'my_property' => 14,
        'perenboom'   => 'zeker',
    ]);

    expect($model->getAttributes())
        ->toEqual([
            'myProperty' => 14,
            'perenboom'  => 'mutated_zeker',
            'bloemkool'  => null,
        ]);
});

