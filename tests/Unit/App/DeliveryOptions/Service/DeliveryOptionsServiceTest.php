<?php


/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

// Enqueue capabilities responses for tests that set a recipient country.
// The default account setup (UsesAccountMock) creates carriers with ALL package types.
// getPackageTypeWeights() makes one capabilities call per allowed package type (5 mapped types).
// Tests without cc won't consume these; UsesSdkApiMock::afterEach() cleans up leftovers.
beforeEach(function () {
    $responseData = [
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE', 'MAILBOX', 'UNFRANKED', 'DIGITAL_STAMP', 'SMALL_PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 23000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ];

    // Enqueue one response per allowed package type (5 mapped V2 types from carrier contract definitions).
    for ($i = 0; $i < 5; $i++) {
        MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse($responseData));
    }
});

it(
    'creates carrier settings',
    function (
        array          $cart,
        CarrierFactory $carrierFactory = null,
        callable       $carrierSettingsFactoryCb = null
    ) {
        $resolvedCarrierFactory = $carrierFactory ?? factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL);
        $fakeCarrier            = $resolvedCarrierFactory->make();

        $carrierSettingsFactory = factory(CarrierSettings::class, $fakeCarrier->carrier)
            ->withDeliveryOptions();

        if ($carrierSettingsFactoryCb) {
            $carrierSettingsFactory = $carrierSettingsFactoryCb($carrierSettingsFactory);
        }

        $carrierSettingsFactory->store();

        factory(Shop::class)
            ->withCarriers(factory(CarrierCollection::class)->push($resolvedCarrierFactory))
            ->store();

        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
        $service = Pdk::get(DeliveryOptionsServiceInterface::class);

        $carrierSettings = $service->createAllCarrierSettings(new PdkCart($cart));

        assertMatchesJsonSnapshot(json_encode($carrierSettings));
    }
)->with([
    'simple' => [
        'cart' => [
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 1,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::DROP_OFF_DELAY => 1,
                        ],
                    ],
                ],
                [
                    'quantity' => 1,
                    'product'  => [
                        'isDeliverable' => true,
                        'weight'        => 1,
                    ],
                ],
            ],
        ],
    ],

    'only virtual products' => [
        'cart' => [
            'lines'   => [
                [
                    'product' => [
                        'isDeliverable' => false,
                    ],
                ],
            ],
        ],
    ],

    'mailbox package' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'NL'],
            ],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'mailbox package that is too heavy for mailbox' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'NL'],
            ],
            'lines'   => [
                [
                    'quantity' => 5,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'mailbox package with fit in mailbox' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'NL'],
            ],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::FIT_IN_MAILBOX => 5,
                            ProductSettings::PACKAGE_TYPE   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'digital stamp' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'NL'],
            ],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'letter' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'NL'],
            ],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'international mailbox that becomes package for non-custom POSTNL' => [
        'cart' => [
            'shippingMethod' => [
                'shippingAddress' => ['cc' => 'FR'],
            ],
            'lines'          => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 500,
                        'isDeliverable' => true,
                        'settings'      => [
                            ProductSettings::PACKAGE_TYPE => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

it('uses international mailbox price when shipping address is non-local', function () {
    $fakeCarrier = factory(Carrier::class)->withCarrier('POSTNL')->make();

    factory(CarrierSettings::class, $fakeCarrier->carrier)
        ->withDeliveryOptions()
        ->withAllowInternationalMailbox(true)
        ->withPriceInternationalMailbox(5)
        ->store();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push(factory(Carrier::class)->withCarrier('POSTNL')))
        ->store();

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'KH'],
        ],
        'lines' => [
            [
                'quantity' => 1,
                'product'  => [
                    'weight'        => 500,
                    'isDeliverable' => true,
                    'settings'      => [
                        ProductSettings::FIT_IN_MAILBOX => 5,
                        ProductSettings::PACKAGE_TYPE   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ],
            ],
        ],
    ]));

    $carrierId   = FrontendData::getLegacyCarrierIdentifier($fakeCarrier->carrier);
    expect($result['carrierSettings'][$carrierId]['pricePackageTypeMailbox'])->toBe(1.05);
});
