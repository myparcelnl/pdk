<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

return [
    'name'             => 'belgie',
    'human'            => 'SendMyParcel',
    'backofficeUrl'    => 'https://backoffice.sendmyparcel.be',
    'localCountry'     => CountryCodes::CC_BE,
    'defaultCarrier'   => Carrier::CARRIER_BPOST_NAME,
    'defaultCarrierId' => Carrier::CARRIER_BPOST_ID,

    /**
     * Carriers that can be used and shown in this platform. Retrieve via Pdk facade.
     *
     * @example Pdk::get('allowedCarriers')
     * @see     /config/pdk-default.php
     */
    'allowedCarriers'  => [
        Carrier::CARRIER_BPOST_NAME,
        Carrier::CARRIER_DPD_NAME,
        Carrier::CARRIER_POSTNL_NAME,
    ],

    'defaultSettings' => [
        CheckoutSettings::ID => [
            CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_MAP,
        ],
    ],
];
