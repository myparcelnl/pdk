<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;

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
                    'classification' => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
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
                    'classification' => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
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
                    'classification' => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
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
                    'classification' => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
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
