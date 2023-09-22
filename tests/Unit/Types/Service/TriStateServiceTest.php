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

function doTest(string $method, array $values)
{
    $service = Pdk::get(TriStateServiceInterface::class);
    $result  = array_pop($values);

    expect($service->{$method}(...array_reverse($values)))->toBe($result);
}

it('coerces values', function (...$values) { doTest('coerce', $values); })->with([
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

it('coerces tri-state values', function (...$values) { doTest('coerce', $values); })->with('triState3');

it('coerces string values', function (...$values) { doTest('coerceString', $values); })->with([
    '-1 -> -1'          => [-1, -1],
    '0 -> _'            => [0, ''],
    '1 -> "1"'          => [1, '1'],
    'empty array -> _'  => [[], ''],
    'empty string -> _' => ['', ''],
    'false -> _'        => [false, ''],
    'null -> _'         => [null, ''],
    'true -> "1"'       => [true, '1'],
    'a, _, b -> b'      => ['a', '', 'b', 'b'],
    '_, _, c, _ -> _'   => ['', '', 'c', '', ''],
]);

it('resolves values', function (...$values) { doTest('resolve', $values); })->with([
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

it('resolves tri-state values', function (...$values) { doTest('resolve', $values); })->with('triState3Coerced');

it('resolves string values', function (...$values) { doTest('resolveString', $values); })->with([
    '-1 -> -1'          => [-1, ''],
    '0 -> _'            => [0, ''],
    '1 -> "1'           => [1, '1'],
    'empty array -> _'  => [[], ''],
    'empty string -> _' => ['', ''],
    'false -> _'        => [false, ''],
    'null -> _'         => [null, ''],
    'true -> "1"'       => [true, '1'],
    '1, _, 2 -> 2'      => ['1', '', '2', '2'],
    '1, 2, 3, _ -> 3'   => ['1', '2', '3', '', '3'],
    'a, _, b -> b'      => ['a', '', 'b', 'b'],
    '_, _, c, _ -> c'   => ['', '', 'c', '', 'c'],
]);
