<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLineFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates customs declaration item from pdk product', function (PdkOrderLineFactory $factory, array $output) {
    factory(CustomsSettings::class)
        ->withPackageContents(CustomsDeclaration::CONTENTS_COMMERCIAL_SAMPLES)
        ->withCountryOfOrigin(CountryCodes::CC_FR)
        ->withCustomsCode('7000')
        ->store();

    $customsDeclaration = CustomsDeclarationItem::fromOrderLine($factory->make());

    expect($customsDeclaration->toArrayWithoutNull())->toEqual($output);
})->with([
    'simple product' => [
        function () {
            return factory(PdkOrderLine::class)->withProduct(factory(PdkProduct::class)->withName('stofzuiger'));
        },

        'output' => [
            'amount'         => 1,
            'classification' => '7000',
            'country'        => CountryCodes::CC_FR,
            'description'    => 'stofzuiger',
            'itemValue'      => [
                'amount'   => 1000,
                'currency' => 'EUR',
            ],
            'weight'         => 0,
        ],
    ],

    'product with custom customs options' => [
        function () {
            return factory(PdkOrderLine::class)->withProduct(
                factory(PdkProduct::class)
                    ->withWeight(800)
                    ->withPrice(4299)
                    ->withSettings(
                        factory(ProductSettings::class)
                            ->withCustomsCode('1234')
                            ->withCountryOfOrigin(CountryCodes::CC_NL)
                    )
            );
        },

        'output' => [
            'amount'         => 1,
            'classification' => '1234',
            'country'        => 'NL',
            'description'    => 'test',
            'itemValue'      => [
                'amount'   => 4299,
                'currency' => 'EUR',
            ],
            'weight'         => 800,
        ],
    ],

    'line with quantity 3' => [
        function () {
            return factory(PdkOrderLine::class)
                ->withQuantity(3)
                ->withProduct(
                    factory(PdkProduct::class)
                        ->withWeight(100)
                        ->withPrice(100)
                );
        },

        'output' => [
            'amount'         => 3,
            'classification' => '7000',
            'country'        => CountryCodes::CC_FR,
            'description'    => 'test',
            'itemValue'      => [
                'amount'   => 100,
                'currency' => 'EUR',
            ],
            'weight'         => 100,
        ],
    ],
]);
