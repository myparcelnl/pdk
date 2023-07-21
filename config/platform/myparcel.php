<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

return [
    'name'             => 'myparcel',
    'human'            => 'MyParcel',
    'backofficeUrl'    => 'https://backoffice.myparcel.nl',
    'localCountry'     => CountryCodes::CC_NL,
    'defaultCarrier'   => Carrier::CARRIER_POSTNL_NAME,
    'defaultCarrierId' => Carrier::CARRIER_POSTNL_ID,

    /**
     * Carriers that can be used and shown in this platform. Retrieve via Pdk facade.
     *
     * @example Pdk::get('allowedCarriers')
     * @see     /config/pdk-default.php
     */
    'allowedCarriers'  => [
        Carrier::CARRIER_DHL_EUROPLUS_NAME,
        Carrier::CARRIER_DHL_FOR_YOU_NAME,
        Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
        Carrier::CARRIER_POSTNL_NAME,
    ],

    'defaultSettings' => [
        CheckoutSettings::ID => [
            CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_LIST,
        ],
    ],
];
