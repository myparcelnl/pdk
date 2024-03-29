<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns correct weight', function (array $input, int $expectedWeight) {
    $customsDeclaration = new CustomsDeclaration($input);

    expect($customsDeclaration->weight)
        ->toEqual($expectedWeight);
})->with([
    'single customs declaration item'    => [
        'input'          => [
            'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
            'invoice'  => '413435',
            'items'    => [
                [
                    'amount'         => 2,
                    'classification' => '0000',
                    'country'        => 'NL',
                    'description'    => null,
                    'itemValue'      => [
                        'amount'   => 10,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 2000,
                ],
            ],
        ],
        'expectedWeight' => 4000,
    ],
    'multiple customs declaration items' => [
        'input'          => [
            'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
            'invoice'  => '413435',
            'items'    => [
                [
                    'amount'         => 1,
                    'classification' => '0000',
                    'country'        => 'FR',
                    'description'    => null,
                    'itemValue'      => [
                        'amount'   => 10,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 1500,
                ],
                [
                    'amount'         => 3,
                    'classification' => '0000',
                    'country'        => 'BE',
                    'description'    => null,
                    'itemValue'      => [
                        'amount'   => 7,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 800,
                ],
            ],
        ],
        'expectedWeight' => 3900,
    ],
    'manual weight'                      => [
        'input'          => [
            'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
            'invoice'  => '413435',
            'weight'   => 12345,
            'items'    => [
                [
                    'amount'         => 3,
                    'classification' => '0000',
                    'country'        => 'BE',
                    'description'    => null,
                    'itemValue'      => [
                        'amount'   => 7,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 800,
                ],
            ],
        ],
        'expectedWeight' => 12345,
    ],
]);
