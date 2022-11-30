<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Base\Support;

use MyParcelNL\Pdk\Base\Support\Arr;

it('can undot an array', function (array $input, array $output) {
    expect(Arr::undot($input))->toBe($output);
})->with([
    'plain object keys'             => [
        'input'  => [
            'a.b.c' => 'd',
            'a.b.e' => 'f',
            'a.b.g' => 'h',
        ],
        'output' => [
            'a' => [
                'b' => [
                    'c' => 'd',
                    'e' => 'f',
                    'g' => 'h',
                ],
            ],
        ],
    ],
    'object keys and array indexes' => [
        'input'  => [
            'a.b.0.c' => 'd',
            'a.b.1.e' => 'f',
            'a.b.2.g' => 'h',
        ],
        'output' => [
            'a' => [
                'b' => [
                    [
                        'c' => 'd',
                    ],
                    [
                        'e' => 'f',
                    ],
                    [
                        'g' => 'h',
                    ],
                ],
            ],
        ],
    ],
]);
