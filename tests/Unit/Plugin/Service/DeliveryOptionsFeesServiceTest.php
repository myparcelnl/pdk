<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsFeesService;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)
            ->constructor([
                CarrierSettings::ID => [
                    'postnl' => [
                        CarrierSettings::PRICE_DELIVERY_TYPE_MORNING  => 1.3,
                        CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD => 4.5,
                        CarrierSettings::PRICE_DELIVERY_TYPE_EVENING  => 3,
                        CarrierSettings::PRICE_DELIVERY_TYPE_MONDAY   => 1.5,
                        CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY => 8.2,
                        CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP   => 0.5,

                        CarrierSettings::PRICE_ONLY_RECIPIENT => 0.7,
                        CarrierSettings::PRICE_SIGNATURE      => 1.1,

                        CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP => 3.6,
                        CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX       => 4.8,
                    ],
                ],
            ]),
    ])
);

it('calculates fees based on delivery options', function (array $input, array $expectation) {
    /** @var DeliveryOptionsFeesService $service */
    $service = Pdk::get(DeliveryOptionsFeesService::class);

    $input += ['carrier' => 'postnl'];

    $fees = $service->getFees(new DeliveryOptions($input));

    expect($fees->toArrayWithoutNull())->toEqual($expectation);
})->with([
    'none' => [
        'input'       => [],
        'expectation' => [
            [
                'id'          => 'delivery_type_standard',
                'translation' => 'delivery_options_delivery_type_standard_title',
                'amount'      => 4.5,
            ],
        ],
    ],

    'delivery type pickup' => [
        'input'       => [DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME],
        'expectation' => [
            [
                'id'          => 'delivery_type_pickup',
                'translation' => 'delivery_options_delivery_type_pickup_title',
                'amount'      => 0.5,
            ],
        ],
    ],

    'delivery type evening' => [
        'input'       => [DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME],
        'expectation' => [
            [
                'id'          => 'delivery_type_evening',
                'translation' => 'delivery_options_delivery_type_evening_title',
                'amount'      => 3,
            ],
        ],
    ],

    'delivery type morning' => [
        'input'       => [DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME],
        'expectation' => [
            [
                'id'          => 'delivery_type_morning',
                'translation' => 'delivery_options_delivery_type_morning_title',
                'amount'      => 1.3,
            ],
        ],
    ],

    'delivery type standard' => [
        'input'       => [DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME],
        'expectation' => [
            [
                'id'          => 'delivery_type_standard',
                'translation' => 'delivery_options_delivery_type_standard_title',
                'amount'      => 4.5,
            ],
        ],
    ],

    'only recipient' => [
        'input'       => [
            'shipmentOptions' => [
                ShipmentOptions::ONLY_RECIPIENT => true,
            ],
        ],
        'expectation' => [
            [
                'id'          => 'delivery_type_standard',
                'translation' => 'delivery_options_delivery_type_standard_title',
                'amount'      => 4.5,
            ],
            [
                'id'          => 'only_recipient',
                'translation' => 'delivery_options_only_recipient_title',
                'amount'      => 0.7,
            ],
        ],
    ],

    'signature' => [
        'input'       => [
            'shipmentOptions' => [
                ShipmentOptions::SIGNATURE => true,
            ],
        ],
        'expectation' => [
            [
                'id'          => 'delivery_type_standard',
                'translation' => 'delivery_options_delivery_type_standard_title',
                'amount'      => 4.5,
            ],
            [
                'id'          => 'signature',
                'translation' => 'delivery_options_signature_title',
                'amount'      => 1.1,
            ],
        ],
    ],

    'same day delivery' => [
        'input'       => [
            'shipmentOptions' => [
                ShipmentOptions::SAME_DAY_DELIVERY => true,
            ],
        ],
        'expectation' => [
            [
                'id'          => 'delivery_type_same_day',
                'translation' => 'delivery_options_delivery_type_same_day_title',
                'amount'      => 8.2,
            ],
        ],
    ],
]);
