<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance());

it('creates carrier settings', function (array $cart) {
    $fakeCarrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_POSTNL_NAME)
        ->make();

    factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
        ->withDeliveryOptionsEnabled(true)
        ->withAllowDeliveryOptions(true)
        ->store();
    TestBootstrapper::hasAccount();

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $carrierSettings = $service->createAllCarrierSettings(new PdkCart($cart));

    assertMatchesJsonSnapshot(json_encode($carrierSettings));
})->with([
    'simple' => [
        'cart' => [
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
            'carrier' => ['name' => 'postnl'],
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
]);

it('creates international mailbox carrier settings', function (array $cart) {
    $fakeCarrier = factory(Carrier::class)
        ->withExternalIdentifier('postnl:123')
        ->make();

    $fakeCarrierConfiguration = factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
        ->withAllowInternationalMailbox(true)
        ->withPriceInternationalMailbox(5)
        ->withDeliveryOptionsEnabled(true)
        ->withAllowDeliveryOptions(true)
        ->store();

    $fakeShop = factory(Shop::class)
        ->withCarriers([$fakeCarrier])
        ->withCarrierConfigurations([$fakeCarrierConfiguration])
        ->make();

    $fakeShopCollection = factory(ShopCollection::class)
        ->push($fakeShop)
        ->make();

    TestBootstrapper::hasAccount(TestBootstrapper::API_KEY_VALID, $fakeShopCollection);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $pdkCart = new PdkCart($cart);

    $carrierSettings = $service->createAllCarrierSettings($pdkCart);

    assertMatchesJsonSnapshot(json_encode($carrierSettings));
})->with([
    'european international mailbox package' => [
        'cart' => [
            'carrier'        => [
                'externalIdentifier' => 'postnl:123',
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
    ],

    'rest of world international mailbox package' => [
        'cart' => [
            'carrier'        => [
                'externalIdentifier' => 'postnl:123',
            ],
            'shippingMethod' => [
                'shippingAddress'     => ['cc' => 'KH'],
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
    ],
]);
