<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

return [
    'name'             => 'myparcel',
    'human'            => 'MyParcel',
    'localCountry'     => CountryService::CC_NL,
    'defaultCarrier'   => Carrier::CARRIER_POSTNL_NAME,
    'defaultCarrierId' => Carrier::CARRIER_POSTNL_ID,

    'settings' => [
        'defaults' => [
            CheckoutSettings::ID => [
                CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_LIST,
            ],
        ],
    ],
];
