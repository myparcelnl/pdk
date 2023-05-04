<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Tests\Mocks\MockBeConcerned;
use MyParcelNL\Pdk\Tests\Mocks\MockClassWithTrait;

it('gets parents of class recursively', function () {
    expect(Utils::getClassParentsRecursive(new MockClassWithTrait()))
        ->toEqual([MockBeConcerned::class => MockBeConcerned::class])
        ->and(Utils::getClassParentsRecursive(MockClassWithTrait::class))
        ->toEqual([MockBeConcerned::class => MockBeConcerned::class])
        ->and(Utils::getClassParentsRecursive(new MockClassWithTrait()))
        ->toEqual([MockBeConcerned::class => MockBeConcerned::class]);
});

it('changes case of array keys', function ($case, $expectation) {
    expect(Utils::changeArrayKeysCase(['snake_case' => 1, 'camelCase' => 2, 'StudlyCase' => 3], $case))
        ->toEqual($expectation);
})->with([
    'to snake_case' => ['snake', ['snake_case' => 1, 'camel_case' => 2, 'studly_case' => 3]],
    'to camelCase'  => ['camel', ['snakeCase' => 1, 'camelCase' => 2, 'studlyCase' => 3]],
    'to StudlyCase' => ['studly', ['SnakeCase' => 1, 'CamelCase' => 2, 'StudlyCase' => 3]],
]);

it('converts id to name', function ($input, $output) {
    $map = [
        'aardappel' => 1,
        'bloemkool' => 2,
        'wortel'    => 3,
    ];

    expect(Utils::convertToName($input, $map))->toBe($output);
})->with([
    [1, 'aardappel'],
    [2, 'bloemkool'],
    [3, 'wortel'],
    ['1', 'aardappel'],
    ['2', 'bloemkool'],
    ['3', 'wortel'],
    ['aardappel', 'aardappel'],
    ['bloemkool', 'bloemkool'],
    ['wortel', 'wortel'],
    ['appel', null],
    [4, null],
    ['4', null],
    [null, null],
]);

it('converts name to id', function ($input, $output) {
    $map = [
        'appel'  => 1,
        'banaan' => 2,
        'peer'   => 3,
    ];

    expect(Utils::convertToId($input, $map))->toBe($output);
})->with([
    ['appel', 1],
    ['banaan', 2],
    ['peer', 3],
    [1, 1],
    [2, 2],
    [3, 3],
    ['1', 1],
    ['2', 2],
    ['3', 3],
    ['aardappel', null],
    [4, null],
    ['4', null],
]);
