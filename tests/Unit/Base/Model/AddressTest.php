<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('correctly transforms deprecated fields', function (array $input, array $output) {
    $address = new Address($input);

    expect(Utils::filterNull($address->toArray()))->toBe($output);
})->with([
    'full_street' => [
        'input'  => [
            'full_street' => 'street 123 b',
        ],
        'output' => [
            'street' => 'street 123 b',
        ],
    ],
    'address1' => [
        'input'  => [
            'address1' => 'Wegstraat 2F',
        ],
        'output' => [
            'street' => 'Wegstraat 2F',
        ],
    ],

    'address2' => [
        'input'  => [
            'address2' => 'Wegstraat 2',
        ],
        'output' => [
            'street' => 'Wegstraat 2',
        ],
    ],

    'address1 and address2' => [
        'input'  => [
            'address1'      => 'street 123',
            'address2'        => 'b',
        ],
        'output' => [
            'street'        => 'street 123 b',
            'streetAdditionalInfo' => 'b',
        ],
    ],
]);
