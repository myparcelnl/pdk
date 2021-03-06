<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class MockConfig implements ConfigInterface
{
    public const  ID_CUSTOM_SUBSCRIPTION_BPOST = 10921;
    public const  ID_CUSTOM_SUBSCRIPTION_DPD   = 10932621;
    private const CONFIG                       = [
        'carriers' => [
            [
                'id'                 => CarrierOptions::CARRIER_POSTNL_ID,
                'name'               => CarrierOptions::CARRIER_POSTNL_NAME,
                'primary'            => 1,
                'type'               => CarrierOptions::TYPE_MAIN,
                'capabilities'       => [
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
                            'ageCheck'             => ['type' => 'boolean'],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean'],
                            'onlyRecipient'        => ['type' => 'boolean'],
                            'return'               => ['type' => 'boolean'],
                            'sameDayDelivery'      => ['type' => 'boolean'],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => [
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
                            'labelDescription'     => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean', 'enum' => [false]],
                            'labelDescription'     => [
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
                        'shipmentOptions' => [
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean', 'enum' => [false]],
                            'labelDescription'     => ['type' => 'null'],
                        ],
                    ],
                    [
                        'packageType'     => [
                            'id'   => 4,
                            'name' => 'digital_stamp',
                        ],
                        'shipmentOptions' => [
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean', 'enum' => [false]],
                            'labelDescription'     => ['type' => 'null'],
                        ],
                    ],
                ],
                'returnCapabilities' => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean', 'enum' => [false]],
                            'insurance'            => ['type' => 'null'],
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id'                 => CarrierOptions::CARRIER_INSTABOX_ID,
                'name'               => CarrierOptions::CARRIER_INSTABOX_NAME,
                'primary'            => 1,
                'type'               => CarrierOptions::TYPE_MAIN,
                'capabilities'       => [
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
                            'ageCheck'             => ['type' => 'boolean'],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean'],
                            'onlyRecipient'        => ['type' => 'boolean'],
                            'return'               => ['type' => 'boolean'],
                            'sameDayDelivery'      => ['type' => 'boolean'],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => ['type' => 'null'],
                            'labelDescription'     => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean'],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => ['type' => 'null'],
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
                'returnCapabilities' => [],
            ],
            [
                'id'                 => CarrierOptions::CARRIER_BPOST_ID,
                'name'               => CarrierOptions::CARRIER_BPOST_NAME,
                'subscriptionId'     => self::ID_CUSTOM_SUBSCRIPTION_BPOST,
                'primary'            => 0,
                'type'               => CarrierOptions::TYPE_CUSTOM,
                'capabilities'       => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean'],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean'],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean'],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => [
                                'type' => 'integer',
                                'enum' => [
                                    0,
                                    500,
                                ],
                            ],
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
                'returnCapabilities' => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean'],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => [
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
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id'                 => CarrierOptions::CARRIER_DPD_ID,
                'name'               => CarrierOptions::CARRIER_DPD_NAME,
                'subscriptionId'     => self::ID_CUSTOM_SUBSCRIPTION_DPD,
                'primary'            => 0,
                'type'               => CarrierOptions::TYPE_CUSTOM,
                'capabilities'       => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                            'signature'            => ['type' => 'boolean', 'enum' => [false]],
                            'insurance'            => [
                                'type' => 'integer',
                                'enum' => [
                                    520,
                                ],
                            ],
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
                'returnCapabilities' => [],
            ],
            [
                'id'                 => CarrierOptions::CARRIER_BPOST_ID,
                'name'               => CarrierOptions::CARRIER_BPOST_NAME,
                'primary'            => 1,
                'type'               => CarrierOptions::TYPE_MAIN,
                'capabilities'       => [
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
                            'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                            'dropOffAtPostalPoint' => ['type' => 'boolean'],
                            'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                            'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                            'return'               => ['type' => 'boolean', 'enum' => [false]],
                            'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                            'saturdayDelivery'     => ['type' => 'boolean'],
                            'signature'            => ['type' => 'boolean'],
                            'insurance'            => [
                                'type' => 'integer',
                                'enum' => [
                                    0,
                                    500,
                                ],
                            ],
                            'labelDescription'     => [
                                'type'      => 'string',
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                ],
                'returnCapabilities' => [],
            ],
        ],
    ];

    /**
     * @param  string $key
     *
     * @return array|\ArrayAccess|mixed
     */
    public function get(string $key)
    {
        return Arr::get(self::CONFIG, $key);
    }
}
