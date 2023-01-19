<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

return [
    'name'             => 'belgie',
    'human'            => 'SendMyParcel',
    'localCountry'     => CountryService::CC_BE,
    'defaultCarrier'   => Carrier::CARRIER_BPOST_NAME,
    'defaultCarrierId' => Carrier::CARRIER_BPOST_ID,

    'settings' => [
        'defaults' => [
            CheckoutSettings::ID => [
                CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_MAP,
            ],
        ],
    ],
];
