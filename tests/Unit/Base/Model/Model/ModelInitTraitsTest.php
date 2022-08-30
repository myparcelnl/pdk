<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;

it('initializes traits', function () {
    expect((new MockMutateModel())->myProperty)->toBe(1);
});
