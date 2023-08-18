<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Mock\Model\MockMutateModel;

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

