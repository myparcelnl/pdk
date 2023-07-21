<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;

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

it('creates customs declaration item from pdk product', function (array $input, array $output) {
    $customsDeclaration = CustomsDeclarationItem::fromProduct(new PdkProduct($input));

    expect($customsDeclaration->toArray())->toEqual($output);
})->with([
    'product' => [
        'input'  => [
            'name'     => 'Test product',
            'weight'   => 1000,
            'settings' => [
                'customsCode'     => '1234',
                'countryOfOrigin' => 'NL',
            ],
        ],
        'output' => [
            'amount'         => 1,
            'classification' => '1234',
            'country'        => 'NL',
            'description'    => 'Test product',
            'itemValue'      => [
                'amount'   => 0,
                'currency' => 'EUR',
            ],
            'weight'         => 1000,
        ],
    ],
]);
