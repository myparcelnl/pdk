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
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsFactory;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance());

it(
    'creates carrier settings',
    function (
        array          $cart,
        CarrierFactory $carrierFactory = null,
        callable       $carrierSettingsFactoryCb = null
    ) {
        $fakeCarrier = ($carrierFactory ?? factory(Carrier::class)->withName(Carrier::CARRIER_POSTNL_NAME))
            ->make();

        $carrierSettingsFactory = factory(CarrierSettings::class, FrontendData::getLegacyIdentifier($fakeCarrier->externalIdentifier))
            ->withDeliveryOptions();

        if ($carrierSettingsFactoryCb) {
            $carrierSettingsFactory = $carrierSettingsFactoryCb($carrierSettingsFactory);
        }

        $carrierSettingsFactory->store();

        factory(Shop::class)
            ->withCarriers(factory(CarrierCollection::class)->push($carrierFactory))
            ->store();

        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
        $service = Pdk::get(DeliveryOptionsServiceInterface::class);

        $carrierSettings = $service->createAllCarrierSettings(new PdkCart($cart));

        assertMatchesJsonSnapshot(json_encode($carrierSettings));
    }
)->with([
    'simple' => [
        'cart' => [
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier' => ['name' => 'POSTNL'],
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
            'carrier'        => ['name' => 'POSTNL'],
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

    'custom POSTNL: eu mailbox package' => [
        'cart'                   => [
            'carrier'        => [
                'externalIdentifier' => 'POSTNL:123',
            ],
            'shippingMethod' => [
                'shippingAddress'     => ['cc' => 'FR'],
                'allowedPackageTypes' => ['mailbox'],
            ],
            'lines'          => [
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
        'carrierFactory'         => function () {
            return factory(Carrier::class)->withExternalIdentifier('POSTNL:123');
        },
        'carrierSettingsFactory' => function () {
            return function (CarrierSettingsFactory $factory) {
                return $factory
                    ->withAllowInternationalMailbox(true)
                    ->withPriceInternationalMailbox(5);
            };
        },
    ],

    'custom POSTNL: be mailbox package' => [
        'cart'                   => [
            'carrier'        => [
                'externalIdentifier' => 'POSTNL:123',
            ],
            'shippingMethod' => [
                'shippingAddress'     => ['cc' => 'BE'],
                'allowedPackageTypes' => ['mailbox'],
            ],
            'lines'          => [
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
        'carrierFactory'         => function () {
            return factory(Carrier::class)->withExternalIdentifier('POSTNL:123');
        },
        'carrierSettingsFactory' => function () {
            return function (CarrierSettingsFactory $factory) {
                return $factory
                    ->withAllowInternationalMailbox(true)
                    ->withPriceInternationalMailbox(5);
            };
        },
    ],

    'custom POSTNL: row mailbox package' => [
        'cart'                   => [
            'carrier'        => [
                'externalIdentifier' => 'POSTNL:123',
            ],
            'shippingMethod' => [
                'shippingAddress'     => ['cc' => 'KH'],
                'allowedPackageTypes' => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME],
            ],
            'lines'          => [
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
        'carrierFactory'         => function () {
            return factory(Carrier::class)->withExternalIdentifier('POSTNL:123');
        },
        'carrierSettingsFactory' => function () {
            return function (CarrierSettingsFactory $factory) {
                return $factory
                    ->withAllowInternationalMailbox(true)
                    ->withPriceInternationalMailbox(5);
            };
        },
    ],
]);
