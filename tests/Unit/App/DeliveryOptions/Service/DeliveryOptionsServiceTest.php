<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('checkout');

usesShared(
    new UsesMockPdkInstance([
        PdkSettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            CarrierSettings::ID => [
                Carrier::CARRIER_POSTNL_NAME => [
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
                ],
            ],
        ]),
    ])
);

it('creates carrier settings', function (array $cart) {
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
