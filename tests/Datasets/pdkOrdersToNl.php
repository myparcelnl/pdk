<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

dataset('pdkOrdersDomestic', [
    'single order' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '247',
                'recipient'          => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'full_street' => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
            ]),
        ];
    },

    'two orders' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '245',
                'deliveryOptions'    => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => 'package',
                    'labelAmount' => 2,
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_NL,
                    'street'     => 'Pietjestraat',
                    'number'     => '35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
            ]),

            new PdkOrder([
                'externalIdentifier' => '247',
                'recipient'          => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'full_street' => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                ],
            ]),
        ];
    },
]);
