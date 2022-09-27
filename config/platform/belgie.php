<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

return [
    'name'           => 'belgie',
    'human'          => 'SendMyParcel',
    'localCountry'   => CountryService::CC_BE,
    'defaultCarrier' => CarrierOptions::CARRIER_BPOST_NAME,
];
