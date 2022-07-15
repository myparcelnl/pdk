<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Factory\CarrierFactory;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;

PdkFactory::create(MockConfig::DEFAULT_CONFIG);

const MOCK_CONFIG = [
    'carriers' => [
        [
            'id'               => 1,
            'name'             => 'postnl',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
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
                        'options'       => [
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
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 2,
                        'name'         => 'mailbox',
                        'options'      => [
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 3,
                        'name'         => 'letter',
                        'options'      => [],
                        'requirements' => [],
                    ],
                    [
                        'id'           => 4,
                        'name'         => 'digital_stamp',
                        'options'      => [
                            'weightClasses' => [
                                [0, 20],
                                [20, 50],
                                [50, 100],
                                [100, 350],
                                [350, 2000],
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [
                [
                    'packageTypes' => [
                        [
                            'id'            => 1,
                            'name'          => 'package',
                            'deliveryTypes' => [
                                [
                                    'id'   => 2,
                                    'name' => 'standard',
                                ],
                            ],
                            'options'       => [
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
                            'id'      => 2,
                            'name'    => 'mailbox',
                            'options' => [
                                'labelDescription' => [
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
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
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
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
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 2,
                        'name'         => 'mailbox',
                        'options'      => [
                            'signature'        => ['type' => 'boolean'],
                            'sameDayDelivery'  => ['type' => 'boolean'],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
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
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
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
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [
                [
                    'packageTypes' => [
                        [
                            'id'            => 1,
                            'name'          => 'package',
                            'deliveryTypes' => [
                                [
                                    'id'   => 2,
                                    'name' => 'standard',
                                ],
                            ],
                            'options'       => [
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
                            'id'      => 2,
                            'name'    => 'mailbox',
                            'options' => [
                                'labelDescription' => [
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
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
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
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
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 31500,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
    ],
];

it('throws empty carrier and exception', function () {
    $carrier = CarrierFactory::create('thisisnocarrier', MOCK_CONFIG);
    expect($carrier->getName())
        ->toBe(null)
        ->and($carrier->getType())
        ->toBe(null)->and(DefaultLogger::getLogs())
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
