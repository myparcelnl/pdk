<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection, PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Mocks\MockTraitModel;

it('initializes traits on model', function () {
    $model = new MockTraitModel();

    expect($model->cat1)
        ->toBe('Gouda')
        ->and($model->cat2)
        ->toBe('Mocha');
});
