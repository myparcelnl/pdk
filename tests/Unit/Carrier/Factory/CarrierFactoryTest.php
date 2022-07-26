<?php
/** @noinspection PhpUnhandledExceptionInspection,PhpUndefinedMethodInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Factory\CarrierFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Sdk\src\Support\Arr;

const MOCK_CONFIG = [
    'carriers' => [
        [
            'id'            => Carrier::CARRIER_POSTNL_ID,
            'name'          => Carrier::CARRIER_POSTNL_NAME,
            'primary'       => 1,
            'type'          => Carrier::TYPE_VALUE_MAIN,
            'options'       => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 1,
                            'name' => 'morning',
                        ],
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                        [
                            'id'   => 3,
                            'name' => 'evening',
                        ],
                        [
                            'id'   => 4,
                            'name' => 'pickup',
                        ],
                    ],
                    'shipmentOptions' => [
                        'signature'        => true,
                        'insurance'        => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                100,
                                250,
                                500,
                                1000,
                                1500,
                                2000,
                                2500,
                                3000,
                                3500,
                                4000,
                                4500,
                                5000,
                            ],
                        ],
                        'ageCheck'         => true,
                        'onlyRecipient'    => true,
                        'return'           => true,
                        'sameDayDelivery'  => true,
                        'largeFormat'      => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageType'     => [
                        'id'   => 2,
                        'name' => 'mailbox',
                    ],
                    'shipmentOptions' => [
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageType'     => [
                        'id'   => 3,
                        'name' => 'letter',
                    ],
                    'shipmentOptions' => [],
                    'requirements'    => [],
                ],
                [
                    'packageType'     => [
                        'id'   => 4,
                        'name' => 'digital_stamp',
                    ],
                    'shipmentOptions' => [
                        'weightClasses' => [
                            [0, 20],
                            [20, 50],
                            [50, 100],
                            [100, 350],
                            [350, 2000],
                        ],
                    ],
                ],
            ],
            'returnOptions' => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'signature'        => ['type' => 'boolean'],
                        'insurance'        => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                100,
                                250,
                                500,
                                1000,
                                1500,
                                2000,
                                2500,
                                3000,
                                3500,
                                4000,
                                4500,
                                5000,
                            ],
                        ],
                        'return'           => ['type' => 'boolean'],
                        'ageCheck'         => ['type' => 'boolean'],
                        'onlyRecipient'    => ['type' => 'boolean'],
                        'sameDayDelivery'  => ['type' => 'boolean'],
                        'largeFormat'      => ['type' => 'boolean'],
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageType'     => [
                        'id'   => 2,
                        'name' => 'mailbox',
                    ],
                    'shipmentOptions' => [
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'            => Carrier::CARRIER_INSTABOX_ID,
            'name'          => Carrier::CARRIER_INSTABOX_NAME,
            'primary'       => 1,
            'type'          => Carrier::TYPE_VALUE_MAIN,
            'options'       => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'signature'        => true,
                        'ageCheck'         => true,
                        'onlyRecipient'    => true,
                        'return'           => true,
                        'sameDayDelivery'  => true,
                        'largeFormat'      => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageType'     => [
                        'id'   => 2,
                        'name' => 'mailbox',
                    ],
                    'shipmentOptions' => [
                        'signature'        => true,
                        'sameDayDelivery'  => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions' => [],
        ],
        [
            'id'             => Carrier::CARRIER_BPOST_ID,
            'name'           => Carrier::CARRIER_BPOST_NAME,
            'subscriptionId' => 10921,
            'primary'        => 0,
            'type'           => Carrier::TYPE_VALUE_CUSTOM,
            'options'        => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'insurance'            => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                500,
                            ],
                        ],
                        'signature'            => true,
                        'saturdayDelivery'     => true,
                        'dropOffAtPostalPoint' => true,
                        'return'               => true,
                        'labelDescription'     => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'  => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'signature'        => true,
                        'insurance'        => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                100,
                                250,
                                500,
                                1000,
                                1500,
                                2000,
                                2500,
                                3000,
                                3500,
                                4000,
                                4500,
                                5000,
                            ],
                        ],
                        'largeFormat'      => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'             => Carrier::CARRIER_DPD_ID,
            'name'           => Carrier::CARRIER_DPD_NAME,
            'subscriptionId' => 10932621,
            'primary'        => 0,
            'type'           => Carrier::TYPE_VALUE_CUSTOM,
            'options'        => [
                [
                    'packageType'     => [
                        'id'   => 1,
                        'name' => 'package',
                    ],
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'insurance'        => [
                            'type' => 'integer',
                            'enum' => [
                                520,
                            ],
                        ],
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'  => [],
        ],
    ],
];

beforeEach(function () {
    PdkFactory::create(MockConfig::DEFAULT_CONFIG);
});

it('creates main carrier by name', function () {
    $carrier = CarrierFactory::create(Carrier::CARRIER_POSTNL_NAME, MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe($carrier::CARRIER_POSTNL_NAME)
        ->and($carrier->getType())
        ->toBe($carrier::TYPE_VALUE_MAIN);
});

it('creates main carrier by carrierId', function () {
    $carrier = CarrierFactory::create(Carrier::CARRIER_INSTABOX_ID, MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe($carrier::CARRIER_INSTABOX_NAME)
        ->and($carrier->getType())
        ->toBe($carrier::TYPE_VALUE_MAIN);
});

it('creates custom carrier by subscriptionId', function () {
    $carrier = CarrierFactory::create(10932621, MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe($carrier::CARRIER_DPD_NAME)
        ->and($carrier->getType())
        ->toBe($carrier::TYPE_VALUE_CUSTOM);
});

it('returns same carrier object', function () {
    $carrier           = CarrierFactory::create(10932621, MOCK_CONFIG);
    $testCarrierObject = CarrierFactory::create($carrier, MOCK_CONFIG);

    expect($testCarrierObject)
        ->toEqual($carrier);
});

it('returns complete carrier object', function () {
    $carrier = CarrierFactory::create(Carrier::CARRIER_INSTABOX_ID, MOCK_CONFIG);

    $array = Arr::dot($carrier->toArray());

    expect($array)
        ->toEqual(
            [
                'id'                                                   => Carrier::CARRIER_INSTABOX_ID,
                'name'                                                 => Carrier::CARRIER_INSTABOX_NAME,
                'primary'                                              => 1,
                'type'                                                 => Carrier::TYPE_VALUE_MAIN,
                'returnOptions'                                        => [],
                'human'                                                => null,
                'subscriptionId'                                       => null,
                'isDefault'                                            => null,
                'optional'                                             => null,
                'label'                                                => null,
                'options.0.packageType.id'                             => 1,
                'options.0.packageType.name'                           => 'package',
                'options.0.deliveryTypes.0.id'                         => 2,
                'options.0.deliveryTypes.0.name'                       => 'standard',
                'options.0.shipmentOptions.ageCheck'                   => true,
                'options.0.shipmentOptions.insurance'                  => null,
                'options.0.shipmentOptions.labelDescription.type'      => 'string',
                'options.0.shipmentOptions.labelDescription.minLength' => 0,
                'options.0.shipmentOptions.labelDescription.maxLength' => 45,
                'options.0.shipmentOptions.largeFormat'                => true,
                'options.0.shipmentOptions.onlyRecipient'              => true,
                'options.0.shipmentOptions.return'                     => true,
                'options.0.shipmentOptions.sameDayDelivery'            => true,
                'options.0.shipmentOptions.signature'                  => true,
                'options.1.packageType.id'                             => 2,
                'options.1.packageType.name'                           => 'mailbox',
                'options.1.deliveryTypes'                              => [],
                'options.1.shipmentOptions.ageCheck'                   => null,
                'options.1.shipmentOptions.insurance'                  => null,
                'options.1.shipmentOptions.labelDescription.type'      => 'string',
                'options.1.shipmentOptions.labelDescription.minLength' => 0,
                'options.1.shipmentOptions.labelDescription.maxLength' => 45,
                'options.1.shipmentOptions.largeFormat'                => null,
                'options.1.shipmentOptions.onlyRecipient'              => null,
                'options.1.shipmentOptions.return'                     => null,
                'options.1.shipmentOptions.sameDayDelivery'            => true,
                'options.1.shipmentOptions.signature'                  => true,
            ]
        );
});

it('creates empty carrier and log', function () {
    $carrier = CarrierFactory::create('thisisnocarrier', MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe(null)
        ->and($carrier->getType())
        ->toBe(null)
        ->and(DefaultLogger::getLogs())
        ->toBe([
            [
                'level'   => 'warning',
                'message' => "[PDK]: Could not find any carrier inside config",
                'context' => [
                    'carrier' => 'thisisnocarrier',
                    'config'  => MOCK_CONFIG,
                ],
            ],
        ]);
});
