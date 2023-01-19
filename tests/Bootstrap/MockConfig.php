<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

class MockConfig implements ConfigInterface
{
    public const  ID_CUSTOM_SUBSCRIPTION_BPOST = 10921;
    public const  ID_CUSTOM_SUBSCRIPTION_DPD   = 10932621;
    private const CONFIG                       = [
        'carriers' => [
            [
                'id'                 => Carrier::CARRIER_POSTNL_ID,
                'name'               => Carrier::CARRIER_POSTNL_NAME,
                'primary'            => 1,
                'type'               => Carrier::TYPE_MAIN,
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
                'id'                 => Carrier::CARRIER_INSTABOX_ID,
                'name'               => Carrier::CARRIER_INSTABOX_NAME,
                'primary'            => 1,
                'type'               => Carrier::TYPE_MAIN,
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
                'id'                 => Carrier::CARRIER_BPOST_ID,
                'name'               => Carrier::CARRIER_BPOST_NAME,
                'subscriptionId'     => self::ID_CUSTOM_SUBSCRIPTION_BPOST,
                'primary'            => 0,
                'type'               => Carrier::TYPE_CUSTOM,
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
                'id'                 => Carrier::CARRIER_DPD_ID,
                'name'               => Carrier::CARRIER_DPD_NAME,
                'subscriptionId'     => self::ID_CUSTOM_SUBSCRIPTION_DPD,
                'primary'            => 0,
                'type'               => Carrier::TYPE_CUSTOM,
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
                'id'                 => Carrier::CARRIER_BPOST_ID,
                'name'               => Carrier::CARRIER_BPOST_NAME,
                'primary'            => 1,
                'type'               => Carrier::TYPE_MAIN,
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
        if (! Arr::has(self::CONFIG, $key)) {
            /** @var \MyParcelNL\Pdk\Base\Config $config */
            $config = Pdk::get(Config::class);
            return $config->get($key);
        }

        return Arr::get(self::CONFIG, $key);
    }
}
