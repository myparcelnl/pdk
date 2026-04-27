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
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock);

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
            'shippingAddress'     => ['cc' => 'KH'],
            'allowedPackageTypes' => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME],
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

it('uses global carrier settings when a carrier-specific row is missing', function () {
    $carrierFactory = factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL);
    $fakeCarrier    = $carrierFactory->make();

    $globalCarrierSettings = new CarrierSettings([
        'id'                                      => SettingsManager::KEY_ALL,
        CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
        CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
        CarrierSettings::ALLOW_STANDARD_DELIVERY  => true,
        CarrierSettings::ALLOW_MORNING_DELIVERY   => true,
        CarrierSettings::ALLOW_PICKUP_DELIVERY    => true,
    ]);

    Pdk::get(PdkSettingsRepositoryInterface::class)
        ->store(Pdk::get('createSettingsKey')(CarrierSettings::ID), [
            SettingsManager::KEY_ALL => $globalCarrierSettings->toStorableArray(),
        ]);

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrierFactory))
        ->store();

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(new PdkCart([
        'lines' => [
            [
                'quantity' => 1,
                'product'  => [
                    'weight'        => 1,
                    'isDeliverable' => true,
                ],
            ],
        ],
    ]));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($fakeCarrier->carrier);

    expect($result['carrierSettings'][$carrierId])
        ->toMatchArray([
            'allowDeliveryOptions'   => true,
            'allowStandardDelivery'  => true,
            'allowMorningDelivery'   => true,
            'allowPickupLocations'   => true,
        ]);
});
