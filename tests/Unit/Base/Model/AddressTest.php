<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Support\Utils;

it('correctly transforms deprecated fields', function (array $input, array $output) {
    $address = new Address($input);

    expect(Utils::filterNull($address->toArray()))->toBe($output);
})->with([
    'full_street'            => [
        'input'  => [
            'full_street' => 'street 123 b',
        ],
        'output' => [
            'address1' => 'street 123 b',
        ],
    ],
    'street_additional_info' => [
        'input'  => [
            'street_additional_info' => '2F',
        ],
        'output' => [
            'address2' => '2F',
        ],
    ],

    'street and number' => [
        'input'  => [
            'street' => 'street',
            'number' => '123',
        ],
        'output' => [
            'address1' => 'street 123',
        ],
    ],

    'street, number and number_suffix' => [
        'input'  => [
            'street'        => 'street',
            'number'        => '123',
            'number_suffix' => 'b',
        ],
        'output' => [
            'address1' => 'street 123 b',
        ],
    ],

    'street, number and box_number' => [
        'input'  => [
            'street'     => 'street',
            'number'     => '123',
            'box_number' => 'b',
        ],
        'output' => [
            'address1' => 'street 123 b',
        ],
    ],
]);
