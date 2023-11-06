<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\CustomsDeclarationCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('calculates customs declaration', function (PdkOrderFactory $factory, ?array $expectation = null) {
    $reset = mockPdkProperty('orderCalculators', [CustomsDeclarationCalculator::class]);

    factory(CustomsSettings::class)
        ->withPackageContents(CustomsDeclaration::CONTENTS_GIFTS)
        ->withCountryOfOrigin(CountryCodes::CC_DE)
        ->withCustomsCode('96020000')
        ->store();

    $order = $factory->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    if (null === $expectation) {
        expect($newOrder->customsDeclaration)->toBeNull();
    } else {
        expect($newOrder->customsDeclaration->toArrayWithoutNull())->toEqual($expectation);
    }

    $reset();
})->with([
    'simple ROW order' => [
        'order'  => function () {
            return factory(PdkOrder::class)->toTheUnitedStates();
        },
        'result' => [
            'contents' => 4,
            'invoice'  => 'PDK-1',
            'items'    => [
                [
                    'amount'         => 1,
                    'classification' => '96020000',
                    'country'        => 'DE',
                    'description'    => 'test',
                    'itemValue'      => [
                        'amount'   => 1000,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 0,
                ],
            ],
            'weight'   => 1,
        ],
    ],

    'complex ROW order' => [
        'order'  => function () {
            $product = factory(PdkProduct::class)
                ->withPrice(1249)
                ->withWeight(300)
                ->withSettings(
                    factory(ProductSettings::class)
                        ->withCustomsCode('123456')
                        ->withCountryOfOrigin(CountryCodes::CC_FR)
                )
                ->make();

            // Not deliverable so should not show up on the customs declaration
            $product2 = factory(PdkProduct::class)
                ->withPrice(895)
                ->withWeight(150)
                ->withIsDeliverable(false)
                ->make();

            return factory(PdkOrder::class)
                ->toTheUnitedStates()
                ->withLines([
                    factory(PdkOrderLine::class)
                        ->withProduct($product)
                        ->withVat((int) ceil(1249 * 0.21))
                        ->withQuantity(3),
                    factory(PdkOrderLine::class)
                        ->withProduct($product2)
                        ->withVat((int) ceil(895 * 0.21))
                        ->withQuantity(2),
                ]);
        },
        'result' => [
            'contents' => CustomsSettings::PACKAGE_CONTENTS_GIFTS,
            'invoice'  => 'PDK-1',
            'weight'   => 900,
            'items'    => [
                [
                    'amount'         => 3,
                    'classification' => '123456',
                    'country'        => 'FR',
                    'description'    => 'test',
                    'itemValue'      => [
                        'amount'   => 1249,
                        'currency' => 'EUR',
                    ],
                    'weight'         => 300,
                ],
            ],
        ],
    ],

    'domestic order' => [
        function () {
            return factory(PdkOrder::class);
        },
    ],

    'BE order' => [
        function () {
            return factory(PdkOrder::class)->toBelgium();
        },
    ],

    'EU order' => [
        function () {
            return factory(PdkOrder::class)->toFrance();
        },
    ],
]);
