<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Datasets;

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

const KEY_DEFAULT          = 'defaultSetting';
const KEY_PRODUCT          = 'productSetting';
const KEY_DELIVERY_OPTIONS = 'deliveryOption';

$frontendShipmentOptions = [
    'only recipient' => [
        [
            KEY_DEFAULT          => CarrierSettings::EXPORT_ONLY_RECIPIENT,
            KEY_PRODUCT          => ProductSettings::EXPORT_ONLY_RECIPIENT,
            KEY_DELIVERY_OPTIONS => ShipmentOptions::ONLY_RECIPIENT,
        ],
    ],

    'signature' => [
        [
            KEY_DEFAULT          => CarrierSettings::EXPORT_SIGNATURE,
            KEY_PRODUCT          => ProductSettings::EXPORT_SIGNATURE,
            KEY_DELIVERY_OPTIONS => ShipmentOptions::SIGNATURE,
        ],
    ],
];

dataset('frontend shipment options', $frontendShipmentOptions);

dataset(
    'all shipment options',
    [
        'age check'    => [
            [
                KEY_DEFAULT          => CarrierSettings::EXPORT_AGE_CHECK,
                KEY_PRODUCT          => ProductSettings::EXPORT_AGE_CHECK,
                KEY_DELIVERY_OPTIONS => ShipmentOptions::AGE_CHECK,
            ],
        ],
        'large format' => [
            [
                KEY_DEFAULT          => CarrierSettings::EXPORT_LARGE_FORMAT,
                KEY_PRODUCT          => ProductSettings::EXPORT_LARGE_FORMAT,
                KEY_DELIVERY_OPTIONS => ShipmentOptions::LARGE_FORMAT,
            ],
        ],
        'return'       => [
            [
                KEY_DELIVERY_OPTIONS => ShipmentOptions::RETURN,
                KEY_DEFAULT          => CarrierSettings::EXPORT_RETURN,
                KEY_PRODUCT          => ProductSettings::EXPORT_RETURN,
            ],
        ],
    ] + $frontendShipmentOptions
);

