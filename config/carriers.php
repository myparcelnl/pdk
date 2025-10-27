<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;

return [
    [
        'id'    => Carrier::CARRIER_POSTNL_ID,
        'name'  => Carrier::CARRIER_POSTNL_NAME,
        'human' => 'PostNL',
        'type'  => Carrier::TYPE_MAIN,
    ],
    [
        'id'    => Carrier::CARRIER_BPOST_ID,
        'name'  => Carrier::CARRIER_BPOST_NAME,
        'human' => 'bpost',
    ],
    [
        'id'    => Carrier::CARRIER_CHEAP_CARGO_ID,
        'name'  => Carrier::CARRIER_CHEAP_CARGO_NAME,
        'human' => 'Cheap Cargo',
    ],
    [
        'id'    => Carrier::CARRIER_DPD_ID,
        'name'  => Carrier::CARRIER_DPD_NAME,
        'human' => 'DPD',
    ],
    [
        'id'    => Carrier::CARRIER_INSTABOX_ID,
        'name'  => Carrier::CARRIER_INSTABOX_NAME,
        'human' => 'Instabox',
    ],
    [
        'id'    => Carrier::CARRIER_DHL_ID,
        'name'  => Carrier::CARRIER_DHL_NAME,
        'human' => 'DHL',
    ],
    [
        'id'    => Carrier::CARRIER_BOL_COM_ID,
        'name'  => Carrier::CARRIER_BOL_COM_NAME,
        'human' => 'Bol.com',
    ],

    [
        'id'    => Carrier::CARRIER_DHL_FOR_YOU_ID,
        'name'  => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        'human' => 'DHL For You',
    ],
    [
        'id'    => Carrier::CARRIER_DHL_PARCEL_CONNECT_ID,
        'name'  => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
        'human' => 'DHL Parcel Connect',
    ],
    [
        'id'    => Carrier::CARRIER_DHL_EUROPLUS_ID,
        'name'  => Carrier::CARRIER_DHL_EUROPLUS_NAME,
        'human' => 'DHL Europlus',
    ],
    [
        'id'    => Carrier::CARRIER_UPS_STANDARD_ID,
        'name'  => Carrier::CARRIER_UPS_STANDARD_NAME,
        'human' => 'UPS Standard',
    ],
    [
        'id'    => Carrier::CARRIER_UPS_EXPRESS_SAVER_ID,
        'name'  => Carrier::CARRIER_UPS_EXPRESS_SAVER_NAME,
        'human' => 'UPS Express Saver',
    ],
    [
        'id'    => Carrier::CARRIER_GLS_ID,
        'name'  => Carrier::CARRIER_GLS_NAME,
        'human' => 'GLS',
    ],
    [
        'id'    => Carrier::CARRIER_TRUNKRS_ID,
        'name'  => Carrier::CARRIER_TRUNKRS_NAME,
        'human' => 'Trunkrs',
    ],
];
