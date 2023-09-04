<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Types\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('settings', 'tri-state');

usesShared(new UsesMockPdkInstance());

function testCoerce(array $values): void
{
    $service = Pdk::get(TriStateServiceInterface::class);
    $result  = array_pop($values);

    expect($service->coerce(...array_reverse($values)))->toBe($result);
}

function testResolve(array $values): void
{
    $service = Pdk::get(TriStateServiceInterface::class);
    $result  = array_pop($values);

    expect($service->resolve(...array_reverse($values)))->toBe($result);
}

it('coerces values', function (...$values) { testCoerce($values); })->with([
    '-1 -> -1'           => [-1, -1],
    '0 -> 0'             => [0, 0],
    '1 -> 1'             => [1, 1],
    'empty array -> -1'  => [[], -1],
    'empty string -> -1' => ['', -1],
    'false -> 0'         => [false, 0],
    'null -> -1'         => [null, -1],
    'true -> 1'          => [true, 1],
    'a, _, b -> b'       => ['a', '', 'b', 'b'],
    '_, _, c, _ -> c'    => ['', '', 'c', '', 'c'],
]);

it('coerces tri-state values', function (...$values) { testCoerce($values); })->with('triState3');

it('resolves values', function (...$values) { testResolve($values); })->with([
    '-1 -> 0'           => [-1, 0],
    '0 -> 0'            => [0, 0],
    '1 -> 1'            => [1, 1],
    'empty array -> 0'  => [[], 0],
    'empty string -> 0' => ['', 0],
    'false -> 0'        => [false, 0],
    'null -> 0'         => [null, 0],
    'true -> 1'         => [true, 1],
    'a, _, b -> b'      => ['a', '', 'b', 'b'],
    '_, _, c, _ -> c'   => ['', '', 'c', '', 'c'],
]);

it('resolves tri-state values', function (...$values) { testResolve($values); })->with('triState3Coerced');
