<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        PdkSettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)
            ->constructorParameter('settings', [
                CarrierSettings::ID => [
                    'POSTNL' => [
                        SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::MORNING)  => 1.3,
                        SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::STANDARD) => 4.5,
                        SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::EVENING)  => 3,
                        SettingKey::priceDeliveryType(DeliveryOptions::DELIVERY_OPTION_MONDAY)   => 1.5,
                        SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::SAME_DAY)  => 8.2,
                        SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::PICKUP)           => 0.5,

                        (new OnlyRecipientDefinition())->getPriceSettingsKey() => 0.7,
                        (new SignatureDefinition())->getPriceSettingsKey()      => 1.1,
                        (new PriorityDeliveryDefinition())->getPriceSettingsKey() => 2.2,

                        SettingKey::pricePackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP) => 3.6,
                        SettingKey::pricePackageType(RefShipmentPackageTypeV2::MAILBOX)      => 4.8,
                    ],
                ],
            ]),
    ]),
    new UsesAccountMock()
);

it('calculates fees based on delivery options', function (array $input, array $expectation) {
    /** @var DeliveryOptionsFeesService $service */
    $service = Pdk::get(DeliveryOptionsFeesService::class);

    $input += ['carrier' => 'POSTNL'];

    $fees = $service->getFees(new DeliveryOptions($input));

    expect($fees->toArray())->toBe($expectation);
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
                'amount'      => 3.0,
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
                (new OnlyRecipientDefinition())->getShipmentOptionsKey() => true,
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
                (new SignatureDefinition())->getShipmentOptionsKey() => true,
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

    'priority delivery' => [
        'input'       => [
            'shipmentOptions' => [
                (new PriorityDeliveryDefinition())->getShipmentOptionsKey() => true,
            ],
        ],
        'expectation' => [
            [
                'id'          => 'delivery_type_standard',
                'translation' => 'delivery_options_delivery_type_standard_title',
                'amount'      => 4.5,
            ],
            [
                'id'          => 'priority_delivery',
                'translation' => 'delivery_options_priority_delivery_title',
                'amount'      => 2.2,
            ],
        ],
    ],

    'same day delivery' => [
        'input'       => [
            'shipmentOptions' => [
                (new SameDayDeliveryDefinition())->getShipmentOptionsKey() => true,
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
