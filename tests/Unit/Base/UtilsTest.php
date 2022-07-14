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

it('changes case of array keys', function ($case, $expectation) {
    expect(Utils::changeArrayKeysCase(['snake_case' => 1, 'camelCase' => 2, 'StudlyCase' => 3], $case))
        ->toEqual($expectation);
})->with([
    'to snake_case' => ['snake', ['snake_case' => 1, 'camel_case' => 2, 'studly_case' => 3]],
    'to camelCase'  => ['camel', ['snakeCase' => 1, 'camelCase' => 2, 'studlyCase' => 3]],
    'to StudlyCase' => ['studly', ['SnakeCase' => 1, 'CamelCase' => 2, 'StudlyCase' => 3]],
]);
