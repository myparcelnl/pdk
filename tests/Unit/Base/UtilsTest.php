<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Utils;
use MyParcelNL\Pdk\Tests\Mocks\AClass;
use MyParcelNL\Pdk\Tests\Mocks\BeConcerned;

it('gets parents of class recursively', function () {
    expect(Utils::getClassParentsRecursive(new AClass()))
        ->toEqual([BeConcerned::class => BeConcerned::class])
        ->and(Utils::getClassParentsRecursive(AClass::class))
        ->toEqual([BeConcerned::class => BeConcerned::class])
        ->and(Utils::getClassParentsRecursive((object) new AClass()))
        ->toEqual([BeConcerned::class => BeConcerned::class]);
});
