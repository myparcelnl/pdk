<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Factory\CarrierFactory;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;

const MOCK_CONFIG = [
    'carriers' => [
        [
            'id'               => 1,
            'name'             => 'postnl',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
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
                        'ageCheck'         => ['type' => 'boolean'],
                        'onlyRecipient'    => ['type' => 'boolean'],
                        'return'           => ['type' => 'boolean'],
                        'sameDayDelivery'  => ['type' => 'boolean'],
                        'largeFormat'      => ['type' => 'boolean'],
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageTypeId'   => 2,
                    'packageTypeName' => 'mailbox',
                    'shipmentOptions' => [
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageTypeid'   => 3,
                    'packageTypeName' => 'letter',
                    'shipmentOptions' => [],
                    'requirements'    => [],
                ],
                [
                    'packageTypeId'   => 4,
                    'packageTypeName' => 'digital_stamp',
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
            'returnOptions'    => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
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
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageTypeId'   => 2,
                    'packageTypeName' => 'mailbox',
                    'shipmentOptions' => [
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'               => 5,
            'name'             => 'instabox',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
                    'deliveryTypes'   => [
                        [
                            'id'   => 2,
                            'name' => 'standard',
                        ],
                    ],
                    'shipmentOptions' => [
                        'signature'        => ['type' => 'boolean'],
                        'ageCheck'         => ['type' => 'boolean'],
                        'onlyRecipient'    => ['type' => 'boolean'],
                        'return'           => ['type' => 'boolean'],
                        'sameDayDelivery'  => ['type' => 'boolean'],
                        'largeFormat'      => ['type' => 'boolean'],
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageTypeId'   => 2,
                    'packageTypeName' => 'mailbox',
                    'shipmentOptions' => [
                        'signature'        => ['type' => 'boolean'],
                        'sameDayDelivery'  => ['type' => 'boolean'],
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
        [
            'id'               => 2,
            'name'             => 'bpost',
            'contractId'       => 10921,
            'primary'          => 0,
            'type'             => 'custom',
            'recipientOptions' => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
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
                        'signature'            => ['type' => 'boolean'],
                        'saturdayDelivery'     => ['type' => 'boolean'],
                        'dropOffAtPostalPoint' => ['type' => 'boolean'],
                        'return'               => ['type' => 'boolean'],
                        'labelDescription'     => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
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
                        'largeFormat'      => ['type' => 'boolean'],
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
                [
                    'packageTypeId'   => 2,
                    'packageTypeName' => 'mailbox',
                    'shipmentOptions' => [
                        'labelDescription' => [
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'               => 4,
            'name'             => 'dpd',
            'contractId'       => 10932621,
            'primary'          => 0,
            'type'             => 'custom',
            'recipientOptions' => [
                [
                    'packageTypeId'   => 1,
                    'packageTypeName' => 'package',
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
                            'type'          => 'string',
                            'minimumLength' => 0,
                            'maximumLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
    ],
];

it('creates empty carrier and log', function () {
    PdkFactory::create(MockConfig::DEFAULT_CONFIG);

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

it('creates main carrier by name', function () {
    $carrier = CarrierFactory::create('postnl', MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe('postnl')
        ->and($carrier->getType())
        ->toBe('main');
});

it('creates main carrier by carrierId', function () {
    $carrier = CarrierFactory::create(5, MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe('instabox')
        ->and($carrier->getType())
        ->toBe('main');
});

it('creates custom carrier by contractId', function () {
    $carrier = CarrierFactory::create(10932621, MOCK_CONFIG);

    expect($carrier->getName())
        ->toBe('dpd')
        ->and($carrier->getType())
        ->toBe('custom');
});
