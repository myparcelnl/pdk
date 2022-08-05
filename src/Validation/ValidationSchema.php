<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class ValidationSchema implements ConfigInterface
{
    /**
     * 1. MyParcel
     * 2. Carrier (PostNL)  -> $myParcel->getCarrierById(1); // -> set $this->carrier
     * 3. Base country      -> $this->carrier->getBaseCountry(); // cc = 'nl'
     * 4. Get options       -> $this->carrier->getOptionsByDeliveryAddress($cc); // -> nl / eu (fr, de, be) / row
     * -> Search if its a EU country or ROW
     * 5. Get the possible options based on EU/ROW and different country options if available
     */

    // Version 1
    //    public const VALIDATION_SCHEMA  = [
    //        'eu_countries' => [
    //            'NL',
    //            'BE',
    //            'AT',
    //            'BG',
    //            'CZ',
    //            'CY',
    //            'DK',
    //            'EE',
    //            'FI',
    //            'FR',
    //            'DE',
    //            'GR',
    //            'HU',
    //            'IE',
    //            'IT',
    //            'LV',
    //            'LT',
    //            'LU',
    //            'PL',
    //            'PT',
    //            'RO',
    //            'SK',
    //            'SI',
    //            'ES',
    //            'SE',
    //            'XK',
    //        ],
    //        'platforms'    => [
    //            [
    //                'id'          => 1,
    //                'name'        => 'myparcel',
    //                'baseCountry' => [
    //                    'cc' => 'nl',
    //                ],
    //                'carriers'    => [
    //                    [
    //                        'id'                        => 1,
    //                        'name'                      => 'postnl',
    //                        'human'                     => 'Post NL',
    //                        'type'                      => 'main',
    //                        'allowedRecipientCountries' => [
    //                            [
    //                                'cc'           => 'nl',
    //                                'packageTypes' => [
    //                                    [
    //                                        'id'            => 1,
    //                                        'name'          => 'package',
    //                                        'options'       => [
    //                                            'insurance'        => [
    //                                                0,
    //                                                100,
    //                                                250,
    //                                                500,
    //                                                1000,
    //                                                1500,
    //                                                2000,
    //                                                2500,
    //                                                3000,
    //                                                3500,
    //                                                4000,
    //                                                4500,
    //                                                5000,
    //                                            ],
    //                                            'ageCheck'         => [0, 1],
    //                                            'signature'        => [0, 1],
    //                                            'onlyRecipient'    => [0, 1],
    //                                            'return'           => [0, 1],
    //                                            'sameDayDelivery'  => [0, 1],
    //                                            'largeFormat'      => [0, 1],
    //                                            'labelDescription' => ['Pizzadoos'], //TODO: Wat moet hier gebeuren?
    //                                        ],
    //                                        'requirements'  => [
    //                                            'weight'           => [
    //                                                'minimum' => 0,
    //                                                'maximum' => 23000,
    //                                            ],
    //                                            'labelDescription' => [
    //                                                'minLength' => 0,
    //                                                'maxLength' => 45,
    //                                            ],
    //                                        ],
    //                                        'deliveryTypes' => [
    //                                            [
    //                                                'id'    => 2,
    //                                                'name'  => 'standard',
    //                                                'human' => 'standaard bezorging',
    //                                            ],
    //                                            [
    //                                                'id'           => 1,
    //                                                'name'         => 'morning',
    //                                                'human'        => 'Ochtend',
    //                                                'requirements' => [
    //                                                    'ageCheck' => 0,
    //                                                ],
    //                                            ],
    //                                        ],
    //                                    ],
    //                                    [
    //                                        'id'           => 2,
    //                                        'name'         => 'mailbox',
    //                                        'human'        => 'Brievenbus pakketje',
    //                                        'options'      => [],
    //                                        'requirements' => [
    //                                            'weight' => [
    //                                                'minimum' => 0,
    //                                                'maximum' => 2000,
    //                                            ],
    //                                        ],
    //                                    ],
    //                                    [
    //                                        'id'           => 3,
    //                                        'name'         => 'letter',
    //                                        'human'        => 'ongefrankeerd',
    //                                        'options'      => [],
    //                                        'requirements' => [],
    //                                    ],
    //                                    [
    //                                        'id'           => 4,
    //                                        'name'         => 'digitalStamp',
    //                                        'human'        => 'Digitale postzegel',
    //                                        'options'      => [
    //                                            'weight_classes' => [
    //                                                [0, 20],
    //                                                [20, 50],
    //                                                [50, 100],
    //                                                [100, 350],
    //                                                [350, 2000],
    //                                            ],
    //                                        ],
    //                                        'requirements' => [
    //                                            'weight' => [
    //                                                'minimum' => 0,
    //                                                'maximum' => 2000,
    //                                            ],
    //                                        ],
    //                                    ],
    //                                ],
    //                            ],
    //                        ],
    //                    ],
    //                ],
    //            ],
    //        ],
    //    ];
    public const VALIDATION_SCHEMA = [
        'carriers' => [
            [
                'id'           => 1,
                'name'         => 'postnl',
                'base_cc'      => CountryCodes::CC_NL,
                'shippingZone' => [
                    [
                        'cc'           => CountryCodes::CC_NL,
                        'packageTypes' => [
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [
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
                                            'ageCheck'         => [0, 1],
                                            'signature'        => [0, 1],
                                            'onlyRecipient'    => [0, 1],
                                            'return'           => [0, 1],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                        'options' => [
                                            'insurance'        => [
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
                                            'ageCheck'         => [0],
                                            'signature'        => [0, 1],
                                            'onlyRecipient'    => [1],
                                            'return'           => [0, 1],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_EVENING_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                                        'options' => [
                                            'insurance'        => [
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
                                            'ageCheck'         => [0],
                                            'signature'        => [0, 1],
                                            'onlyRecipient'    => [1],
                                            'return'           => [0, 1],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        'options' => [
                                            'insurance'        => [
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
                                            'ageCheck'         => [
                                                'requirements' => [
                                                    'signature'     => 1,
                                                    'onlyRecipient' => 1,
                                                ],
                                                'values'       => [0, 1],
                                            ],
                                            'signature'        => [1],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 23000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'cc'           => CountryCodes::CC_BE,
                        'packageTypes' => [
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [
                                                500,
                                            ],
                                            'ageCheck'         => [0],
                                            'signature'        => [1],
                                            'onlyRecipient'    => [1],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 23000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'cc'           => 'EU',
                        'packageTypes' => [
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [
                                                500,
                                            ],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0, 1],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 23000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'cc'           => 'ROW',
                        'packageTypes' => [
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [
                                                200,
                                            ],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 23000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id'           => 5,
                'name'         => 'instabox',
                'base_cc'      => CountryCodes::CC_NL,
                'shippingZone' => [
                    [
                        'cc'           => CountryCodes::CC_NL,
                        'packageTypes' => [
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0, 1],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0, 1],
                                            'return'           => [0, 1],
                                            'sameDayDelivery'  => [0, 1],
                                            'largeFormat'      => [0, 1],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 23000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                            [
                                'id'            => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                'name'          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                'deliveryTypes' => [
                                    [
                                        'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                        'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        'options' => [
                                            'insurance'        => [0],
                                            'ageCheck'         => [0],
                                            'signature'        => [0],
                                            'onlyRecipient'    => [0],
                                            'return'           => [0],
                                            'sameDayDelivery'  => [0, 1],
                                            'largeFormat'      => [0],
                                            'labelDescription' => [0, 1],
                                        ],
                                    ],
                                ],
                                'requirements'  => [
                                    'weight'           => [
                                        'minimum' => 0,
                                        'maximum' => 2000,
                                    ],
                                    'labelDescription' => [
                                        'minLength' => 0,
                                        'maxLength' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
    public const EU_COUNTRIES      = [
        'NL',
        'BE',
        'AT',
        'BG',
        'CZ',
        'CY',
        'DK',
        'EE',
        'FI',
        'FR',
        'DE',
        'GR',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'PL',
        'PT',
        'RO',
        'SK',
        'SI',
        'ES',
        'SE',
        'XK',
    ];

    /**
     * @param  string $key
     *
     * @return array|\ArrayAccess|mixed
     */
    public function get(string $key)
    {
        return Arr::get(self::VALIDATION_SCHEMA, $key);
    }
}
