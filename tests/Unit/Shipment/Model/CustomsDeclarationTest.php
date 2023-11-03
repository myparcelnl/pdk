<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
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

it('creates customs declaration from pdk order', function () {
    factory(CustomsSettings::class)
        ->withPackageContents(CustomsDeclaration::CONTENTS_GIFTS)
        ->withCountryOfOrigin(CountryCodes::CC_DE)
        ->withCustomsCode('96020000')
        ->store();

    $product = factory(PdkProduct::class)
        ->withPrice(1249)
        ->withWeight(300)
        ->make();

    // Not deliverable so should not show up on the customs declaration
    $product2 = factory(PdkProduct::class)
        ->withPrice(895)
        ->withWeight(150)
        ->withIsDeliverable(false)
        ->make();

    $order = factory(PdkOrder::class)
        ->withLines([
            factory(PdkOrderLine::class)
                ->withProduct($product)
                ->withVat((int) ceil(1249 * 0.21))
                ->withQuantity(3),
            factory(PdkOrderLine::class)
                ->withProduct($product2)
                ->withVat((int) ceil(895 * 0.21))
                ->withQuantity(2),
        ])
        ->make();

    $customsDeclaration = CustomsDeclaration::fromPdkOrder($order);

    expect($customsDeclaration->toArrayWithoutNull())->toEqual([
        'contents' => CustomsSettings::PACKAGE_CONTENTS_GIFTS,
        'invoice'  => 'PDK-1',
        'weight'   => 900,
        'items'    => [
            [
                'amount'         => 3,
                'classification' => '96020000',
                'country'        => 'DE',
                'description'    => $product->name,
                'itemValue'      => [
                    'amount'   => 1249,
                    'currency' => 'EUR',
                ],
                'weight'         => 300,
            ],
        ],
    ]);
});
