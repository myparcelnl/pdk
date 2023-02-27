<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

return [
    'name'           => 'belgie',
    'human'          => 'SendMyParcel',
    'localCountry'   => CountryCodes::CC_BE,
    'defaultCarrier' => CarrierOptions::CARRIER_BPOST_NAME,
];
