<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;

it('throws error when unknown attributes are passed via constructor', function () {
    new MockCastModel(['what' => 'fiets']);
})->throws(InvalidArgumentException::class);
